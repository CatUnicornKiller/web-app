<?php

namespace App\Forms;

use App;
use Nette;
use App\Model\Entity\User;
use App\Model\Entity\ExtraPoints;
use App\Users\UserManager;
use App\Model\Repository\Users;
use App\Model\Repository\Events;
use App\Model\Repository\Faculties;
use App\Model\Repository\EventCoorganizers;
use App\Model\Repository\ExtraPointsRepository;

/**
 * Class containing factory methods mainly concerning officers forms. Alongside
 * factories there can also be success callbacks.
 */
class UsersFormsFactory
{
    use Nette\SmartObject;

    /** @var User */
    private $user;
    /** @var Users */
    private $users;
    /** @var Events */
    private $events;
    /** @var Faculties */
    private $faculties;
    /** @var EventCoorganizers */
    private $eventCoorganizers;
    /** @var ExtraPointsRepository */
    private $extraPointsRepository;
    /** @var App\Users\RolesManager */
    private $rolesManager;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param Users $users
     * @param App\Users\RolesManager $rolesManager
     * @param Faculties $faculties
     * @param Events $events
     * @param EventCoorganizers $eventCoorganizers
     * @param ExtraPointsRepository $extraPointsRepository
     */
    public function __construct(
        UserManager $userManager,
        Users $users,
        App\Users\RolesManager $rolesManager,
        Faculties $faculties,
        Events $events,
        EventCoorganizers $eventCoorganizers,
        ExtraPointsRepository $extraPointsRepository
    ) {

        $this->user = $userManager->getCurrentUser();
        $this->users = $users;
        $this->rolesManager = $rolesManager;
        $this->faculties = $faculties;
        $this->events = $events;
        $this->eventCoorganizers = $eventCoorganizers;
        $this->extraPointsRepository = $extraPointsRepository;
    }

    /**
     * Create form which manages change of the officers role.
     * @param int $id officer identification
     * @param array $rolesDesc list of roles description
     * @return \App\Forms\MyForm
     */
    public function createChangeRoleForm($id, array $rolesDesc)
    {
        $form = new MyForm;
        $form->addRadioList('role', 'Role', $rolesDesc);
        $form->addHidden('id', $id);
        $form->addSubmit('changeRole', 'Change Role');
        $form->onSuccess[] = array($this, 'changeRoleFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the change officer role form.
     * @param \App\Forms\MyForm $form
     * @param array $values form values
     */
    public function changeRoleFormSucceeded(MyForm $form, $values)
    {
        $usr = $this->users->findOfficerOrThrow($values->id);
        $usr->roleModified($this->user, $usr->role, $values->role);
        $usr->role = $values->role;
        $this->users->flush();
    }

    /**
     * Create form for the modification of officers ifmsa credentials.
     * @param int $id identification of the officer
     * @return \App\Forms\MyForm
     */
    public function createModifyIfmsaCredentialsForm($id)
    {
        $form = new MyForm;
        $form->addText('ifmsaUsername', 'IFMSA Username');
        $form->addPassword('ifmsaPassword', 'IFMSA Password');
        $form->addHidden('id', $id);
        $form->addSubmit('send', 'Change IFMSA credentials');
        $form->onSuccess[] = array($this, 'modifyIfmsaCredentialsFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the modify ifmsa credentials form.
     * @param \App\Forms\MyForm $form
     * @param array $values form values
     */
    public function modifyIfmsaCredentialsFormSucceeded(MyForm $form, $values)
    {
        $usr = $this->users->findOfficerOrThrow($values->id);
        $usr->officersProfile->ifmsaUsername = $values->ifmsaUsername;
        $usr->officersProfile->ifmsaPassword = $values->ifmsaPassword;

        $usr->modified($this->user);
        $this->users->flush();
    }

    /**
     * Create modify officers events points form.
     * @param int $id officer identification
     * @return \App\Forms\MyForm
     */
    public function createModifyEventsPointsForm($id)
    {
        $user = $this->users->findOrThrow($id);
        $form = new MyForm;

        $points = $form->addContainer('points');
        $events = $user->organizedEvents;
        foreach ($events as $event) {
            $points->addText($event->id, 'Event Points')
                    ->setType('number')->setValue($event->points)
                    ->setAttribute('class', 'event_points')
                    ->setRequired('Points have to be filled');
        }

        $form->addHidden('id', $id);
        $form->addSubmit('send', 'Modify Points');
        $form->onSuccess[] = array($this, 'modifyEventsPointsFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the modify events points form.
     * @param \App\Forms\MyForm $form
     * @param array $values form values
     */
    public function modifyEventsPointsFormSucceeded(MyForm $form, $values)
    {
        foreach ($values->points as $key => $val) {
            $event = $this->events->findOrThrow($key);
            $event->points = $val;
            $this->events->flush();
        }
    }

    /**
     * Create modify coorganizer events points form.
     * @param int $id officer identification
     * @return \App\Forms\MyForm
     */
    public function createModifyCoorgEventsPointsForm($id)
    {
        $user = $this->users->findOrThrow($id);
        $form = new MyForm;

        $points = $form->addContainer('points');
        $events = $user->coorganizedEvents;
        foreach ($events as $eventCoorg) {
            $points->addText($eventCoorg->id, 'Event Points')
                    ->setType('number')->setValue($eventCoorg->points)
                    ->setAttribute('class', 'event_points')
                    ->setRequired('Points have to be filled');
        }

        $form->addHidden('id', $id);
        $form->addSubmit('send', 'Modify Coorganization Points');
        $form->onSuccess[] = array($this, 'modifyCoorgEventsPointsFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the modify coorganizer events points form.
     * @param \App\Forms\MyForm $form
     * @param array $values form values
     */
    public function modifyCoorgEventsPointsFormSucceeded(MyForm $form, $values)
    {
        foreach ($values->points as $key => $val) {
            $event = $this->eventCoorganizers->findOrThrow($key);
            $event->points = $val;
            $this->events->flush();
        }
    }

    /**
     * Create filtering form for the list of officers. Returns simple form with
     * GET method set.
     * @param int $fac faculty identification
     * @param string $priv privileges identification
     * @return \App\Forms\MySimpleForm
     */
    public function createFilterOfficersForm($fac, $priv)
    {
        $faculties = array();
        foreach ($this->faculties->findAll() as $faculty) {
            $faculties[$faculty->id] = $faculty->facultyName;
        }

        $form = new MySimpleForm;
        $form->setMethod('get');

        $form->addSelect('faculty', 'Faculty', $faculties)
                ->setPrompt('All faculties');
        $form->addSelect('privileges', 'Privileges', $this->rolesManager->getRoles())
                ->setPrompt('All privileges');
        $form->addSubmit('send', 'Filter');


        try {
            $form->setDefaults(array( 'faculty' => $fac, 'privileges' => $priv ));
        } catch (\Exception $e) {
        }

        return $form;
    }

    /**
     * Create add extra points to the officer form.
     * @param User $user
     * @return \App\Forms\MyForm
     */
    public function createAddExtraPointsForm(User $user)
    {
        $form = new MyForm;
        $form->addTextArea('description', 'Description')
                ->setRequired("Description is required")
                ->setMaxLength(1000)
                ->setAttribute('length', 1000);
        $form->addText('points', 'Points')->setType("number")
                ->setDefaultValue(0)
                ->setRequired("Points are required");
        $form->addHidden('id', $user->id);
        $form->addSubmit('send', 'Add Extra Points');
        $form->onSuccess[] = array($this, 'addExtraPointsFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the add extra points form.
     * @param \App\Forms\MyForm $form
     * @param array $values form values
     */
    public function addExtraPointsFormSucceeded(MyForm $form, $values)
    {
        $user = $this->users->findOfficerOrThrow($values->id);
        $extra = new ExtraPoints($user, $this->user, $values->points, $values->description);
        $extra->modified($this->user);
        $this->extraPointsRepository->persist($extra);
    }
}
