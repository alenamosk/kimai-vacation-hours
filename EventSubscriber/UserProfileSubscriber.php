<?php

namespace KimaiPlugin\VacationHoursBundle\EventSubscriber;

use App\Entity\UserPreference;
use App\Event\UserPreferenceEvent;
use App\Repository\TimesheetRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use KimaiPlugin\VacationHoursBundle\Library\VacationHoursCalculator;

class UserProfileSubscriber implements EventSubscriberInterface
{
    public function __construct(private TimeSheetRepository $repository, private Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserPreferenceEvent::class => ['loadUserPreferences', 200],
        ];
    }

    public function loadUserPreferences(UserPreferenceEvent $event)
    {
        if (null === ($user = $event->getUser())) {
            return;
        }

        // Check if the user has the ROLE_ADMIN role
        $isAdmin = $this->security->isGranted('ROLE_ADMIN');

        $event->addPreference(
            (new UserPreference('target-weekly-hours', 32))
                ->setEnabled($isAdmin)
                ->setType(IntegerType::class)
                ->setOptions(['label' => 'Target Weekly Hours' . ($isAdmin ? '' : ' (Read-Only)'), 'attr' => ['readonly' => !$isAdmin]]) // Make readonly if not admin
        );

        $event->addPreference(
            (new UserPreference('target-weekly-start', '1970-01-30'))
                ->setEnabled($isAdmin)
                ->setType(TextType::class)
                ->setOptions(['label'    => 'Target Weekly Start Date' . ($isAdmin ? '' : ' (Read-Only)'), 'attr' => ['readonly' => !$isAdmin]]) // Make readonly if not admin
        );

        $event->addPreference(
            (new UserPreference('yearly-vacation-days', 16))
                ->setEnabled($isAdmin)
                ->setType(NumberType::class)
                ->setOptions(['label' => 'Yearly Vacation Days' . ($isAdmin ? '' : ' (Read-Only)'), 'attr' => ['readonly' => !$isAdmin]]) // Make readonly if not admin
        );

        $event->addPreference(
            (new UserPreference('start-of-period-vacation-hours', 0))
                ->setEnabled($isAdmin)
                ->setType(NumberType::class)
                ->setOptions(['label' => 'Start of Period Vacation Hours' . ($isAdmin ? '' : ' (Read-Only)'), 'attr' => ['readonly' => !$isAdmin]]) // Make readonly if not admin
        );

        $event->addPreference(
            (new UserPreference('extra-vacation-days', 0))
                ->setEnabled($isAdmin)
                ->setType(IntegerType::class)
                ->setOptions(['label' => 'Extra Vacation Days' . ($isAdmin ? '' : ' (Read-Only)'), 'attr' => ['readonly' => !$isAdmin]]) // Make readonly if not admin
	);

	$event->addPreference(
		(new UserPreference('vacation-hours-placeholder-v2', -9))
			->setType(TextType::class)
			->setSection('Vacation Hours')
			->setOptions([
				'label' => 'Current vacation hours',
				'attr' => ['readonly' => true],
				'data' => VacationHoursCalculator::formatHours(VacationHoursCalculator::calculateHours($event->getUser(), $this->repository) ?: 0)
			])
	);
    }
}
