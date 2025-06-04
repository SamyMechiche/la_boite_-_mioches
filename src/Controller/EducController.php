<?php

namespace App\Controller;

use App\Repository\AttendanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EducController extends AbstractController
{
    #[Route('/educator', name: 'app_educ')]
    public function index(AttendanceRepository $attendanceRepository): Response
    {
        $user = $this->getUser();
        $groups = $user->getGroupId();
        
        // Get all attendance records for the current month
        $currentMonth = new \DateTime();
        $attendanceRecords = $attendanceRepository->findByMonth($currentMonth);

        return $this->render('educator/index.html.twig', [
            'groups' => $groups,
            'attendanceRecords' => $attendanceRecords,
            'currentMonth' => $currentMonth,
        ]);
    }
}
