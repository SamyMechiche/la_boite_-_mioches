<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ChildRepository;
use App\Repository\ActivityRepository;
use App\Repository\GroupRepository;
use App\Repository\AttendanceRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(
        UserRepository $userRepository, 
        ChildRepository $childRepository,
        ActivityRepository $activityRepository,
        GroupRepository $groupRepository,
        AttendanceRepository $attendanceRepository,
        MessageRepository $messageRepository
    ): Response
    {
        $users = $userRepository->findAll();
        $groups = $groupRepository->findAll();
        
        $educators = [];
        $parents = [];
        $children = $childRepository->findAll();
        $activities = $activityRepository->findAll();

        // Calculate statistics
        $statistics = [
            'total_educators' => 0,
            'total_parents' => 0,
            'total_children' => count($children),
            'total_activities' => count($activities),
            'total_groups' => count($groups),
            'attendance_rate' => 0,
            'unread_messages' => 0,
            'children_with_allergies' => 0,
            'average_children_per_group' => 0,
            'upcoming_activities' => 0
        ];

        // Process users and count roles
        foreach ($users as $user) {
            $roles = $user->getRoles();
            
            if (in_array('ROLE_ADMIN', $roles)) {
                // Skip admins in the listing as they have their own section
                continue;
            } elseif (in_array('ROLE_EDUCATOR', $roles)) {
                $educators[] = $user;
                $statistics['total_educators']++;
            } else {
                $parents[] = $user;
                $statistics['total_parents']++;
            }
        }

        // Count children with allergies
        foreach ($children as $child) {
            if ($child->getAllergies()) {
                $statistics['children_with_allergies']++;
            }
        }

        // Calculate average children per group
        if (count($groups) > 0) {
            $statistics['average_children_per_group'] = round(count($children) / count($groups), 1);
        }

        // Count upcoming activities
        $now = new \DateTime();
        foreach ($activities as $activity) {
            if ($activity->getBeginningHour() >= $now) {
                $statistics['upcoming_activities']++;
            }
        }

        // Calculate attendance rate for current month
        $currentMonth = new \DateTime();
        $attendanceRecords = $attendanceRepository->findByMonth($currentMonth);
        
        if (count($attendanceRecords) > 0) {
            $totalPossibleAttendance = count($children) * count($attendanceRecords);
            $actualAttendance = array_reduce($attendanceRecords, function($carry, $record) {
                return $carry + ($record->getHalfDay() ? 0.5 : 1);
            }, 0);
            $statistics['attendance_rate'] = round(($actualAttendance / $totalPossibleAttendance) * 100, 1);
        }

        // Get unread messages count
        $statistics['unread_messages'] = $messageRepository->findUnreadCount($this->getUser());

        return $this->render('admin/index.html.twig', [
            'educators' => $educators,
            'parents' => $parents,
            'children' => $children,
            'activities' => $activities,
            'groups' => $groups,
            'statistics' => $statistics
        ]);
    }
}
