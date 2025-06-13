<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Form\AttendanceForm;
use App\Repository\ActivityRepository;
use App\Repository\AttendanceRepository;
use App\Repository\ChildRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parent')]
class ParentController extends AbstractController
{
    #[Route('/', name: 'app_parent', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ChildRepository $childRepository,
        ActivityRepository $activityRepository,
        MessageRepository $messageRepository,
        AttendanceRepository $attendanceRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $children = $childRepository->findBy(['parent' => $user]);
        $upcomingActivities = [];
        $attendanceStats = [];

        // Get upcoming activities for children's groups
        foreach ($children as $child) {
            if ($child->getGroup()) {
                $activities = $activityRepository->createQueryBuilder('a')
                    ->innerJoin('a.GroupId', 'g')
                    ->where('g.id = :groupId')
                    ->andWhere('a.beginning_hour >= :now')
                    ->setParameter('groupId', $child->getGroup()->getId())
                    ->setParameter('now', new \DateTime())
                    ->orderBy('a.beginning_hour', 'ASC')
                    ->getQuery()
                    ->getResult();
                
                $upcomingActivities = array_merge($upcomingActivities, $activities);
            }
        }

        // Sort activities by date
        usort($upcomingActivities, function($a, $b) {
            return $a->getBeginningHour() <=> $b->getBeginningHour();
        });

        // Get recent messages
        $recentMessages = $messageRepository->findRecentMessages($user, 5);

        // Calculate attendance statistics
        foreach ($children as $child) {
            $attendanceRecords = $attendanceRepository->findBy(['child' => $child]);
            $totalDays = count($attendanceRecords);
            $presentDays = 0;
            
            foreach ($attendanceRecords as $record) {
                if ($record->getStatus()->value === 'present') {
                    $presentDays++;
                }
            }
            
            $attendanceStats[$child->getId()] = [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0
            ];
        }

        // Handle attendance form
        $attendance = new Attendance();
        $form = $this->createForm(AttendanceForm::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($attendance);
            $entityManager->flush();

            $this->addFlash('success', 'Attendance record has been added successfully.');
            return $this->redirectToRoute('app_parent');
        }

        return $this->render('parent/index.html.twig', [
            'children' => $children,
            'upcomingActivities' => $upcomingActivities,
            'recentMessages' => $recentMessages,
            'attendanceStats' => $attendanceStats,
            'form' => $form->createView(),
        ]);
    }
}
