<?php

namespace App\Service;

use App\Entity\Child;
use App\Entity\Group;
use App\Enum\AgeGroup;
use App\Repository\GroupRepository;
use DateTime;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\VarDumper\VarDumper;

class GroupAssignmentService
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function assignGroup(Child $child): void
    {
        $ageInMonths = $this->calculateAgeInMonths($child->getBirthDate());
        $ageGroup = $this->determineAgeGroup($ageInMonths);
        
        $this->logger->error('DEBUG - Starting group assignment', [
            'child_id' => $child->getId(),
            'birth_date' => $child->getBirthDate()->format('Y-m-d'),
            'age_in_months' => $ageInMonths,
            'determined_age_group' => $ageGroup->value
        ]);

        // Get all groups and find the matching one
        $groups = $this->groupRepository->findAll();
        
        // Log all groups for debugging
        $this->logger->error('DEBUG - Available groups', [
            'groups' => array_map(fn(Group $g) => [
                'id' => $g->getId(),
                'name' => $g->getName(),
                'age_group' => $g->getAgeGroup()->value,
                'age_group_type' => gettype($g->getAgeGroup()),
                'age_group_class' => get_class($g->getAgeGroup())
            ], $groups)
        ]);

        $matchingGroup = null;
        foreach ($groups as $group) {
            $groupAgeGroup = $group->getAgeGroup();
            $this->logger->error('DEBUG - Comparing groups', [
                'group_id' => $group->getId(),
                'group_age_group' => $groupAgeGroup->value,
                'determined_age_group' => $ageGroup->value,
                'is_match' => $groupAgeGroup === $ageGroup
            ]);
            
            if ($groupAgeGroup === $ageGroup) {
                $matchingGroup = $group;
                break;
            }
        }

        if ($matchingGroup) {
            $this->logger->error('DEBUG - Found matching group', [
                'group_id' => $matchingGroup->getId(),
                'group_name' => $matchingGroup->getName(),
                'group_age_group' => $matchingGroup->getAgeGroup()->value
            ]);
            
            $child->setGroup($matchingGroup);
            $this->entityManager->persist($child);
        } else {
            $this->logger->error('DEBUG - No matching group found', [
                'age_group' => $ageGroup->value,
                'available_groups' => array_map(fn(Group $g) => [
                    'id' => $g->getId(),
                    'name' => $g->getName(),
                    'age_group' => $g->getAgeGroup()->value
                ], $groups)
            ]);
        }
    }

    private function calculateAgeInMonths(DateTime $birthDate): int
    {
        $today = new DateTime();
        $diff = $today->diff($birthDate);
        return ($diff->y * 12) + $diff->m;
    }

    private function determineAgeGroup(int $ageInMonths): AgeGroup
    {
        return match (true) {
            $ageInMonths <= 12 => AgeGroup::Lucioles,
            $ageInMonths <= 24 => AgeGroup::Papillons,
            $ageInMonths <= 36 => AgeGroup::Chevaliers,
            default => AgeGroup::Bazookas,
        };
    }
} 