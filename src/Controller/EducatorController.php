<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Repository\AttendanceRepository;
use App\Repository\ChildRepository;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/educator')]
class EducatorController extends AbstractController
{
    #[Route('/', name: 'app_educator')]
    public function index(
        AttendanceRepository $attendanceRepository, 
        ChildRepository $childRepository,
        ActivityRepository $activityRepository
    ): Response
    {
        $user = $this->getUser();
        $groups = $user->getGroupId();
        
        // Get children for each group
        $groupChildren = [];
        $groupActivities = [];
        $statistics = [
            'total_children' => 0,
            'total_activities' => 0,
            'attendance_rate' => 0,
            'upcoming_activities' => 0
        ];
        
        foreach ($groups as $group) {
            $children = $childRepository->findBy(['group' => $group]);
            $groupChildren[$group->getId()] = $children;
            $statistics['total_children'] += count($children);
            
            // Get activities for this group
            $activities = $activityRepository->createQueryBuilder('a')
                ->innerJoin('a.GroupId', 'g')
                ->where('g.id = :groupId')
                ->setParameter('groupId', $group->getId())
                ->orderBy('a.beginning_hour', 'ASC')
                ->getQuery()
                ->getResult();
            
            $groupActivities[$group->getId()] = $activities;
            $statistics['total_activities'] += count($activities);
            
            // Count upcoming activities (today and future)
            $now = new \DateTime();
            $upcomingActivities = array_filter($activities, function($activity) use ($now) {
                return $activity->getBeginningHour() >= $now;
            });
            $statistics['upcoming_activities'] += count($upcomingActivities);
        }
        
        // Calculate attendance rate for current month
        $currentMonth = new \DateTime();
        $attendanceRecords = $attendanceRepository->findByMonth($currentMonth);
        
        if (count($attendanceRecords) > 0) {
            $totalPossibleAttendance = count($groupChildren) * count($attendanceRecords);
            $actualAttendance = array_reduce($attendanceRecords, function($carry, $record) {
                return $carry + ($record->getHalfDay() ? 0.5 : 1);
            }, 0);
            $statistics['attendance_rate'] = round(($actualAttendance / $totalPossibleAttendance) * 100, 1);
        }

        return $this->render('educator/index.html.twig', [
            'groups' => $groups,
            'groupChildren' => $groupChildren,
            'groupActivities' => $groupActivities,
            'attendanceRecords' => $attendanceRecords,
            'currentMonth' => $currentMonth,
            'statistics' => $statistics
        ]);
    }

    #[Route('/calendar', name: 'app_educator_calendar', methods: ['GET'])]
    public function getCalendarData(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $month = $request->query->get('month', date('m'));
        $year = $request->query->get('year', date('Y'));

        // Get the first and last day of the month
        $firstDay = new \DateTime("$year-$month-01");
        $lastDay = clone $firstDay;
        $lastDay->modify('last day of this month');

        // Get attendance data for the month with children's names
        $qb = $entityManager->createQueryBuilder();
        $qb->select('a.date, c.fname, c.id as child_id, a.half_day')
           ->from(Attendance::class, 'a')
           ->join('a.child', 'c')
           ->where('a.date BETWEEN :firstDay AND :lastDay')
           ->setParameter('firstDay', $firstDay)
           ->setParameter('lastDay', $lastDay)
           ->orderBy('a.date', 'ASC')
           ->addOrderBy('c.fname', 'ASC');

        $attendanceData = $qb->getQuery()->getResult();

        // Format the data for the calendar
        $calendarData = [];
        foreach ($attendanceData as $data) {
            $date = $data['date']->format('Y-m-d');
            if (!isset($calendarData[$date])) {
                $calendarData[$date] = [
                    'count' => 0,
                    'children' => []
                ];
            }
            $calendarData[$date]['count']++;
            $calendarData[$date]['children'][] = [
                'name' => $data['fname'],
                'id' => $data['child_id'],
                'half_day' => $data['half_day']->value
            ];
        }

        return $this->json([
            'calendarData' => $calendarData,
            'month' => $month,
            'year' => $year
        ]);
    }
} 