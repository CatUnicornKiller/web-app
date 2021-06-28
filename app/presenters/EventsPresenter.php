<?php

namespace App\Presenters;

use App;
use App\Model\Entity\EventParticipant;
use App\Model\Repository\Events;
use App\Model\Repository\Faculties;
use App\Model\Repository\EventFiles;
use App\Model\Repository\EventParticipants;
use App\Model\Repository\EventCoorganizers;
use Nette;
use function implode;

/**
 * Events presenter.
 */
class EventsPresenter extends BasePresenter
{
    /**
     * @var App\Forms\EventsFormsFactory
     * @inject
     */
    public $eventsFormsFactory;
    /**
     * @var App\Helpers\ResponseHelper
     * @inject
     */
    public $responseHelpers;
    /**
     * @var App\Helpers\Table\EventsTableFactory
     * @inject
     */
    public $eventsTableFactory;
    /**
     * @var Events
     * @inject
     */
    public $events;
    /**
     * @var Faculties
     * @inject
     */
    public $faculties;
    /**
     * @var EventFiles
     * @inject
     */
    public $eventFiles;
    /**
     * @var EventParticipants
     * @inject
     */
    public $eventParticipants;
    /**
     * @var EventCoorganizers
     * @inject
     */
    public $eventCoorganizers;

    protected function createComponentModifyEventForm($event)
    {
        $form = $this->eventsFormsFactory->createModifyEventForm($event);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $this->flashMessage('Event successfully modified.');
            $this->redirect('Events:detail', $values->id);
        };
        return $form;
    }

    private function createUploadEventImageForm($id)
    {
        $form = $this->eventsFormsFactory->createUploadEventImageForm($id);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            if ($this->isAjax()) {
                $this->redrawControl('event_img');
            } else {
                $this->flashMessage('Image successfully uploaded');
                $this->redirect('Events:detail', $values->id);
            }
        };
        $form->onError[] = function (App\Forms\MyForm $form) {
            if ($this->isAjax()) {
                $httpResponse = $this->getHttpResponse();
                $httpResponse->setCode(Nette\Http\Response::S500_INTERNAL_SERVER_ERROR);
                $this->sendResponse(new Nette\Application\Responses\TextResponse(
                    implode(", ", $form->getErrors())
                ));
            }
        };
        return $form;
    }

    private function createFilterEventsAjaxForm(
        $startDate,
        $endDate,
        $socialProgram,
        $academicQuality,
        $facultyId
    ) {

        $form = $this->eventsFormsFactory->createFilterEventsForm(
            $startDate,
            $endDate,
            $socialProgram,
            $academicQuality,
            $facultyId
        );
        $form->elementPrototype->addClass('ajax');
        return $form;
    }

    private function createFilterEventsListAjaxForm($startDate, $endDate, $faculty)
    {
        $form = $this->eventsFormsFactory->createFilterEventsListForm($startDate, $endDate, $faculty);
        $form->elementPrototype->addClass('ajax');
        return $form;
    }

    public function actionDefault($startDate, $endDate, $faculty, $socialProgram, $academicQuality)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "view")) {
            $this->error('Access Denied');
        }

        $facultyTyped = $faculty ? $this->faculties->get($faculty) : null;
        $startDateHolder = $this->dateHelper->createFromDateOrFirstDayOfMonth($startDate);
        $endDateHolder = $this->dateHelper->createFromDateOrLastDayOfMonth($endDate);

        $events = $this->events->getEventsListQuery(
            $this->currentUser->getFaculty(),
            $startDateHolder->typed,
            $endDateHolder->typed,
            $socialProgram,
            $academicQuality,
            $facultyTyped
        )->getResult();
        $eventIdsList = array();
        foreach ($events as $event) {
            $eventIdsList[] = $event->id;
        }

        $this['filterEventsForm'] = $this->createFilterEventsAjaxForm(
            $startDateHolder->textual,
            $endDateHolder->textual,
            $socialProgram,
            $academicQuality,
            $faculty
        );

        $this->template->eventsList = $events;
        $this->template->eventIdsList = $eventIdsList;
        $this->template->canGenerateTable = $this->user->isAllowed("Events", "generateTable");

        if ($this->isAjax()) {
            $this->redrawControl('eventsList');
        }
    }

    public function actionDetail($id)
    {
        $event = $this->events->findOrThrow($id);

        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "view") ||
                !$this->myAuthorizator->isAllowedEvent('view', $event)) {
            $this->error('Access Denied');
        }

        $this['uploadEventImageForm'] = $this->createUploadEventImageForm($id);

        $this->template->event = $event;
        $this->template->now = date_create();
        $this->template->myParticipation = $this->currentUser->getParticipatedEvent($event);
        $this->template->canEdit = $this->user->isAllowed('Event', 'edit') &&
                $this->myAuthorizator->isAllowedEvent('edit', $event);
        $this->template->canDelete = $this->user->isAllowed('Event', 'delete') &&
                $this->myAuthorizator->isAllowedEvent('delete', $event);
        $this->template->canDeleteCoorg = $this->myAuthorizator->isAllowedEvent('delCoorg', $event);
        $this->template->canAddCoorg = $event->getCoorganizers()->count() < 2 &&
                $this->myAuthorizator->isAllowedEvent('addCoorg', $event);
        $this->template->canDeleteImg = $this->user->isAllowed('Event', 'edit') &&
                $this->myAuthorizator->isAllowedEvent('edit', $event);
        $this->template->canGenerateParticipants = $event->getParticipants()->count() > 0 &&
                $this->myAuthorizator->isAllowedEvent('generateParticipants', $event);
    }

    public function actionAddEvent()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "add")) {
            $this->error('Access Denied');
        }

        $this['eventForm'] = $this->eventsFormsFactory->createAddEventForm();
    }

    public function actionDeleteEvent($id)
    {
        $event = $this->events->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "delete") ||
                !$this->myAuthorizator->isAllowedEvent('delete', $event)) {
            $this->error('Access Denied');
        }

        $event->modified($this->currentUser);
        $event->delete();
        $this->events->flush();

        $this->flashMessage('Event was successfully deleted.');
        $this->redirect('Events:');
    }

    public function actionDeleteEventImage($id)
    {
        $file = $this->eventFiles->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "edit") ||
                !$this->myAuthorizator->isAllowedEvent('edit', $file->getEvent())) {
            $this->error('Access Denied');
        }

        $file->modified($this->currentUser);
        $file->delete();
        $this->eventFiles->flush();

        $this->flashMessage('File successfully deleted.');
        $this->redirect('Events:detail', $file->getEvent()->getId());
    }

    public function actionModifyEvent($id)
    {
        $event = $this->events->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", 'edit') ||
                !$this->myAuthorizator->isAllowedEvent('edit', $event)) {
            $this->error('Access Denied');
        }

        $this['eventForm'] = $this->createComponentModifyEventForm($event);
    }

    public function actionSignUp($id)
    {
        $event = $this->events->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "sign") ||
                !$this->myAuthorizator->isAllowedEvent('signUp', $event)) {
            $this->error('Access Denied');
        }

        if (date_create() > $event->getSignupDeadline()) {
            $this->error('Signup deadline exceeded');
        }

        $freeCapacity = $event->getCapacity() - $event->getParticipants()->count();
        if ($freeCapacity > 0 || $event->getCapacity() == 0) {
            $this->disableDoctrineFilters();
            $eventParticipant = $this->currentUser->getParticipatedEvent($event);
            $this->enableDoctrineFilters();

            if ($eventParticipant) {
                $eventParticipant->deleted = false;
                $eventParticipant->modified($this->currentUser);
                $this->eventParticipants->flush();
            } else {
                $eventParticipant = new EventParticipant($this->currentUser, $event);
                $eventParticipant->modified($this->currentUser);
                $this->eventParticipants->persist($eventParticipant);
            }

            $this->flashMessage('You were signed up succesfully!');
        } else {
            $this->flashMessage('This event is full! You cannot sign up.');
        }
        $this->redirect('Events:detail', $id);
    }

    public function actionUnSign($id)
    {
        $signed = $this->eventParticipants->findOrThrow($id);
        if ($signed->getUser() !== $this->currentUser) {
            $this->error("Unsigning wrong event and person");
        }

        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "sign") ||
                !$this->myAuthorizator->isAllowedEvent('unSign', $signed->getEvent())) {
            $this->error('Access Denied');
        }

        $signed->modified($this->currentUser);
        $signed->delete();
        $this->eventParticipants->flush();

        $this->flashMessage('You were succesfully unsigned from event!');
        $this->redirect('Events:detail', $signed->getEvent()->getId());
    }

    public function actionAddCoorganizer($id)
    {
        $event = $this->events->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "edit") ||
                !$this->myAuthorizator->isAllowedEvent('addCoorg', $event)) {
            $this->error('Access Denied');
        }

        $form = $this->eventsFormsFactory->createAddCoorganizerForm($event);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Coorganizer was successfully added.');
            $form->presenter->redirect('Events:detail', $values->id);
        };
        $this['addCoorganizerForm'] = $form;
    }

    public function actionDeleteCoorganizer($id)
    {
        $coorg = $this->eventCoorganizers->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "edit") ||
                !$this->myAuthorizator->isAllowedEvent('delCoorg', $coorg->getEvent())) {
            $this->error('Access Denied');
        }

        $coorg->modified($this->currentUser);
        $coorg->delete();
        $this->eventCoorganizers->flush();

        $this->flashMessage('Coorganizer was successfully deleted.');
        $this->redirect('Events:detail', $coorg->getEvent()->getId());
    }

    public function renderGenerateParticipantsTable($id)
    {
        $event = $this->events->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Event", "edit") ||
                !$this->myAuthorizator->isAllowedEvent('generateParticipants', $event)) {
            $this->error("Access Denied");
        }

        // headers and response sending
        $this->responseHelpers->setXlsxFileResponse($this->getHttpResponse(), 'table.xlsx');

        // table generation
        $this->eventsTableFactory->createParticipantsTable($id);
        $this->terminate();
    }

    public function renderGenerateEventsTable(array $events)
    {
        if (!$this->isLoggedIn() || !$events ||
                !$this->user->isAllowed("Events", "generateTable")) {
            $this->error("Access Denied");
        }

        // headers and response sending
        $this->responseHelpers->setXlsxFileResponse($this->getHttpResponse(), 'table.xlsx');

        // table generation
        $this->eventsTableFactory->createEventsTable($events);
        $this->terminate();
    }

    public function actionList($orderby, $order, $faculty, $startDate, $endDate)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("AllEvents", "view")) {
            $this->error('Access Denied');
        }

        $startDateHolder = $this->dateHelper->createFromDateOrFirstDayOfMonth($startDate);
        $endDateHolder = $this->dateHelper->createFromDateOrLastDayOfMonth($endDate);
        $facultyTyped = $faculty ? $this->faculties->get($faculty) : null;

        $events = $this->events->getAllEventsListQuery(
            $orderby,
            $order,
            $facultyTyped,
            $startDateHolder->typed,
            $endDateHolder->typed
        )->getResult();
        $eventIdsList = array();
        foreach ($events as $event) {
            $eventIdsList[] = $event->id;
        }

        $this['filterEventsListForm'] =
                $this->createFilterEventsListAjaxForm(
                    $startDateHolder->textual,
                    $endDateHolder->textual,
                    $faculty
                );

        $this->template->eventsList = $events;
        $this->template->eventIdsList = $eventIdsList;

        if ($this->isAjax()) {
            $this->redrawControl('eventsList');
        }
    }
}
