<?php

namespace App\Forms;

use App\Helpers\ConfigParams;
use App\Helpers\StringHelper;
use Exception;
use Nette;
use Nette\Application\UI\Form;
use App;
use App\Model\Entity\User;
use App\Model\Entity\Event;
use App\Model\Entity\EventFile;
use App\Model\Entity\EventCoorganizer;
use App\Users\UserManager;
use App\Model\Repository\Events;
use App\Model\Repository\Users;
use App\Model\Repository\Faculties;
use App\Model\Repository\EventFiles;
use App\Model\Repository\EventCoorganizers;

/**
 * Class containing factory methods for forms mainly concerning events
 * management. Alongside factories there can also be success callbacks.
 */
class EventsFormsFactory
{
    use Nette\SmartObject;

    /** @var User */
    private $user;
    /** @var StringHelper */
    private $stringHelpers;
    /** @var ConfigParams */
    private $configParams;
    /** @var Events */
    private $events;
    /** @var Users */
    private $users;
    /** @var Faculties */
    private $faculties;
    /** @var EventFiles */
    private $eventFiles;
    /** @var EventCoorganizers */
    private $eventCoorganizers;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param Events $events
     * @param StringHelper $stringHelpers
     * @param ConfigParams $configParams
     * @param Faculties $faculties
     * @param EventFiles $eventFiles
     * @param Users $users
     * @param EventCoorganizers $eventCoorganizers
     */
    public function __construct(
        UserManager $userManager,
        Events $events,
        StringHelper $stringHelpers,
        ConfigParams $configParams,
        Faculties $faculties,
        EventFiles $eventFiles,
        Users $users,
        EventCoorganizers $eventCoorganizers
    ) {

        $this->user = $userManager->getCurrentUser();
        $this->events = $events;
        $this->stringHelpers = $stringHelpers;
        $this->configParams = $configParams;
        $this->faculties = $faculties;
        $this->eventFiles = $eventFiles;
        $this->users = $users;
        $this->eventCoorganizers = $eventCoorganizers;
    }

    /**
     * Get faculties list for the selection box.
     * @return array
     */
    private function getFacultiesSelectList()
    {
        $faculties = $this->faculties->findAll();
        $facArr = array();
        foreach ($faculties as $fac) {
            $facArr[$fac->getId()] = $fac->getFacultyName();
        }
        return $facArr;
    }

    /**
     * Create base event editation form.
     * @return MyForm
     */
    private function createEventForm()
    {
        $form = new MyForm();
        $form->addCheckboxList(
            'visibleToFaculties',
            'Visible to Faculties',
            $this->getFacultiesSelectList()
        )
                ->setRequired("At least one faculty has to be selected");
        $form->addCheckbox('socialProgram', 'Social Program');
        $form->addCheckbox('academicQuality', 'Academic Quality');
        $form->addText('startDate', 'Start Date')
                ->setRequired('Start Date is required')
                ->setHtmlAttribute('class', 'datepicker')
                ->setDefaultValue(date('j. n. Y'));
        $form->addText('startTime', 'Start Time')
                ->setRequired('Start Time is required')
                ->setHtmlAttribute('class', 'timepicker')
                ->setDefaultValue('00:00');
        $form->addText('endDate', 'End Date')
                ->setRequired('End Date is required')
                ->setHtmlAttribute('class', 'datepicker')
                ->setDefaultValue(date('j. n. Y'));
        $form->addText('endTime', 'End Time')
                ->setRequired('End Time is required')
                ->setHtmlAttribute('class', 'timepicker')
                ->setDefaultValue('23:59');
        $form->addText('signupDeadlineDate', 'Signup Deadline Date')
                ->setRequired('Signup Deadline Date is required')
                ->setHtmlAttribute('class', 'datepicker')
                ->setDefaultValue(date('j. n. Y'));
        $form->addText('signupDeadlineTime', 'Signup Deadline Time')
                ->setRequired('Signup Deadline Time is required')
                ->setHtmlAttribute('class', 'timepicker')
                ->setDefaultValue('00:00');
        $form->addText('eventName', 'Event Name')
                ->setRequired('Event Name is required')
                ->addRule(Form::MAX_LENGTH, 'Event Name is too long', 100)
                ->setHtmlAttribute('length', 100);
        $form->addText('place', 'Place')
                ->setRequired('Place is required')
                ->addRule(Form::MAX_LENGTH, 'Place is too long', 1000)
                ->setHtmlAttribute('length', 1000);
        $form->addText('price', 'Price in CZK (Zero = For free)')
                ->setDefaultValue('0')
                ->setHtmlType("number")
                ->addRule(Form::INTEGER, 'Price is not a number')
                ->setRequired('Price is required');
        $form->addText('capacity', 'Capacity (Zero = Unlimited)')
                ->setDefaultValue('0')
                ->setHtmlType("number")
                ->addRule(Form::INTEGER, 'Capacity is not a number')
                ->setRequired('Capacity is required');
        $form->addUpload('eventLogo', 'Event Logo');
        $form->addOriginalTextArea('eventDescription', 'Event Description')
                ->setHtmlAttribute('id', 'event_desc')
                ->setHtmlAttribute('class', 'tinymce');
        return $form;
    }

    /**
     * Check event editation form for the errors.
     * @param MyForm $form
     * @param object $values
     * @return boolean true if form contains errors
     */
    private function checkEventForm(MyForm $form, $values)
    {
        $error = false;

        $time = date_create_from_format("j. n. Y H:i", $values->startDate . ' ' . $values->startTime);
        $endTime = date_create_from_format("j. n. Y H:i", $values->endDate . ' ' . $values->endTime);
        $deadline = date_create_from_format("j. n. Y H:i", $values->signupDeadlineDate . ' ' . $values->signupDeadlineTime);
        if ($time === false || $endTime === false) {
            $form->addError('Start or end datetime is in bad format');
            $error = true;
        }
        if ($deadline === false) {
            $form->addError('Signup Deadline datetime is in bad format');
            $error = true;
        }
        if (strlen($values->eventDescription) > 20000) {
            $form->addError('Too long event description');
            $error = true;
        }
        if ($deadline > $time) {
            $form->addError('Signup Deadline cannot be bigger than Start datetime');
            $error = true;
        }
        if ($time > $endTime) {
            $form->addError('Start datetime is bigger than End datetime');
            $error = true;
        }
        if (!$values->socialProgram && !$values->academicQuality) {
            $form->addError("Social Program or Academic Quality has to be chosen");
            $error = true;
        }
        if ($values->eventLogo->isOk() && !$values->eventLogo->isImage()) {
            $form->addError('Uploaded file is not an image');
            $error = true;
        }
        if ($values->eventLogo->isOk() && $values->eventLogo->getSize() > (5 * 1024 * 1024)) {
            $form->addError('Uploaded image has to be up to 5MB');
            $error = true;
        }

        return $error;
    }

    /**
     * Create add event form.
     * @return MyForm
     */
    public function createAddEventForm()
    {
        $form = $this->createEventForm();
        $form->addSubmit('send', 'Add Event');

        $form->setDefaults(array('visibleToFaculties' => $this->user->getFaculty()->getId()));

        $form->onSuccess[] = array($this, 'addEventFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the add event form.
     * @param MyForm $form
     * @param object $values
     */
    public function addEventFormSucceeded(MyForm $form, $values)
    {
        if ($this->checkEventForm($form, $values)) {
            return;
        }

        $time = date_create_from_format("j. n. Y H:i", $values->startDate . ' ' . $values->startTime);
        $endTime = date_create_from_format("j. n. Y H:i", $values->endDate . ' ' . $values->endTime);
        $deadline = date_create_from_format("j. n. Y H:i", $values->signupDeadlineDate . ' ' . $values->signupDeadlineTime);
        $logo = "";
        if ($values->eventLogo->isOk()) {
            $logoExt = pathinfo($values->eventLogo->sanitizedName, PATHINFO_EXTENSION);
            $logo = 'logo.' . $logoExt;
        }

        // prepare faculties
        $visibleToFaculties = array();
        foreach ($values->visibleToFaculties as $facultyId) {
            $visibleToFaculties[] = $this->faculties->findOrThrow($facultyId);
        }

        $event = new Event(
            $this->user,
            $time,
            $endTime,
            $deadline,
            $values->eventName,
            $values->eventDescription,
            $logo,
            $visibleToFaculties,
            $values->place,
            $values->price,
            $values->capacity,
            $values->socialProgram,
            $values->academicQuality
        );
        $event->modified($this->user);
        $this->events->persist($event);

        // create images directory
        mkdir(getcwd() . $this->configParams->eventImgDir . $event->getId() . '/');

        // move uploaded file
        if ($values->eventLogo->isOk()) {
            $values->eventLogo->move(getcwd() . $this->configParams->eventImgDir .
                    $event->getId() . '/' . $logo);
        }

        $form->presenter->flashMessage('Event successfully created.');
        $form->presenter->redirect('Events:detail', $event->getId());
    }

    /**
     * Create modification form for the event.
     * @param Event $event
     * @return MyForm
     */
    public function createModifyEventForm(Event $event)
    {
        $form = $this->createEventForm();
        $form->addHidden('id', $event->getId());
        $form->addSubmit('send', 'Modify Event');
        $form->onSuccess[] = array($this, 'modifyEventFormSucceeded');

        $form->setDefaults(array('startDate' => $event->getDate()->format('j. n. Y'),
            'startTime' => $event->getDate()->format('H:i'), 'endDate' => $event->getEndDate()->format('j. n. Y'),
            'endTime' => $event->getEndDate()->format('H:i'), 'signupDeadlineDate' => $event->getSignupDeadline()->format('j. n. Y'),
            'signupDeadlineTime' => $event->getSignupDeadline()->format('H:i'),
            'eventName' => $event->getEventName(),
            'eventDescription' => $event->getEventDescription(),
            'visibleToFaculties' => $event->getVisibleToFacultiesIds(), 'place' => $event->getPlace(),
            'price' => $event->getPrice(), 'capacity' => $event->getCapacity(),
            'socialProgram' => $event->getSocialProgram(), 'academicQuality' => $event->getAcademicQuality()));

        return $form;
    }

    /**
     * Success callback for the modify event form.
     * @param MyForm $form
     * @param object $values
     */
    public function modifyEventFormSucceeded(MyForm $form, $values)
    {
        if ($this->checkEventForm($form, $values)) {
            return;
        }

        $time = date_create_from_format("j. n. Y H:i", $values->startDate . ' ' . $values->startTime);
        $endTime = date_create_from_format("j. n. Y H:i", $values->endDate . ' ' . $values->endTime);
        $deadline = date_create_from_format("j. n. Y H:i", $values->signupDeadlineDate . ' ' . $values->signupDeadlineTime);
        $logo = "";
        if ($values->eventLogo->isOk()) {
            $logoExt = pathinfo($values->eventLogo->sanitizedName, PATHINFO_EXTENSION);
            $logo = 'logo.' . $logoExt;
        }

        $event = $this->events->findOrThrow($values->id);
        $event->setDate($time);
        $event->setEndDate($endTime);
        $event->setSignupDeadline($deadline);
        $event->setEventName($values->eventName);
        $event->setEventDescription($values->eventDescription);
        if (!empty($logo)) {
            $event->setEventLogo($logo);
        }
        $event->setPlace($values->place);
        $event->setPrice($values->price);
        $event->setCapacity($values->capacity);
        $event->setSocialProgram($values->socialProgram);
        $event->setAcademicQuality($values->academicQuality);
        $event->modified($this->user);

        // prepare and store faculties
        $visibleToFaculties = array();
        foreach ($values->visibleToFaculties as $facultyId) {
            $visibleToFaculties[] = $this->faculties->findOrThrow($facultyId);
        }
        $event->replaceVisibleToFaculties($visibleToFaculties);

        // move uploaded file
        if ($values->eventLogo->isOk()) {
            $values->eventLogo->move(getcwd() . $this->configParams->eventImgDir .
                    $values->id . '/' . $logo);
        }

        // and at the end flush all changes made to event
        $this->events->flush();
    }

    /**
     * Create filter events calendar form. Returns form with GET method set.
     * @param string $startDate
     * @param string $endDate
     * @param bool $socialProgram
     * @param bool $academicQuality
     * @param int $facultyId
     * @return MyForm
     */
    public function createFilterEventsForm(
        $startDate,
        $endDate,
        $socialProgram,
        $academicQuality,
        $facultyId
    ) {

        $form = new MyForm;
        $form->setMethod('get');

        $form->addSelect('faculty', 'Organizer Faculty', $this->getFacultiesSelectList())
                ->setPrompt('All');
        $form->addCheckbox('socialProgram', 'Social Program');
        $form->addCheckbox('academicQuality', 'Academic Quality');
        $form->addText('startDate', 'Start Date');
        $form->addText('endDate', 'End Date');

        $form->addSubmit('send', 'Filter');

        try {
            $form->setDefaults(array(
                'startDate' => $startDate,
                'endDate' => $endDate,
                'socialProgram' => $socialProgram,
                'academicQuality' => $academicQuality,
                'faculty' => $facultyId
            ));
        } catch (Exception $e) {
        }

        return $form;
    }

    /**
     * Create filter events list form. Returns form with GET method set.
     * @param string $startDate
     * @param string $endDate
     * @param int $facultyId
     * @return MyForm
     */
    public function createFilterEventsListForm($startDate, $endDate, $facultyId)
    {
        $form = new MyForm;
        $form->setMethod('get');

        $form->addSelect('faculty', 'Organizer Faculty', $this->getFacultiesSelectList())
                        ->setPrompt('All');
        $form->addText('startDate', 'Start Date');
        $form->addText('endDate', 'End Date');
        $form->addSubmit('send', 'Filter');

        try {
            $form->setDefaults(array(
                'startDate' => $startDate,
                'endDate' => $endDate,
                'faculty' => $facultyId
            ));
        } catch (Exception $e) {
        }

        return $form;
    }

    /**
     * Create add coorganizer form.
     * @param Event $event
     * @return MyForm
     */
    public function createAddCoorganizerForm($event)
    {
        $form = new MyForm();
        $coorganizers = $this->users->getOfficers();

        $coorgList = array();
        foreach ($coorganizers as $coorg) {
            $coorgList[$coorg->getId()] = $coorg->getFirstname() . ' ' . $coorg->getSurname() . ' (' . $coorg->getUsername() . ')';
        }
        $form->addRadioList('coorganizer', 'Choose Coorganizer', $coorgList)
                ->setRequired('You have to choose coorganizer');

        $form->addHidden('id', $event->getId());
        $form->addSubmit('send', 'Add Coorganizer');
        $form->onSuccess[] = array($this, 'addCoorganizerFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the add coorganizer form.
     * @param MyForm $form
     * @param object $values
     */
    public function addCoorganizerFormSucceeded(MyForm $form, $values)
    {
        $event = $this->events->findOrThrow($values->id);
        $user = $this->users->findOrThrow($values->coorganizer);
        $coorg = $user->getCoorganizedEvent($event);

        if ($coorg) {
            $form->addError('Officer is already coorganizer for this event.');
            return;
        }
        if ($this->user->getId() == $values->coorganizer) {
            $form->addError('You cannot add yourself as coorganizer.');
            return;
        }
        if ($event->getCoorganizers()->count() >= 2) {
            $form->addError('Another coorganizer cannot be assigned. Two is maximum.');
            return;
        }

        $coorganizer = new EventCoorganizer($user, $event);
        $coorganizer->modified($this->user);
        $this->eventCoorganizers->persist($coorganizer);
    }

    /**
     * Create upload event image form.
     * @param int $id event identification
     * @return MyForm
     */
    public function createUploadEventImageForm($id)
    {
        $form = new MyForm();
        $form->getElementPrototype()->class = 'dropzone';
        $form->getElementPrototype()->id = 'imgUploadDropzone';
        $form->addUpload('image');
        $form->addHidden('id', $id);
        $form->onSuccess[] = array($this, 'uploadEventImageFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the upload event image form.
     * @param MyForm $form
     * @param object $values
     */
    public function uploadEventImageFormSucceeded(MyForm $form, $values)
    {
        $tmpFile = $values->image->getTemporaryFile();
        if (empty($tmpFile)) {
            $form->addError('File was not uploaded properly');
            return;
        }

        if ($values->image->isOk() && $values->image->isImage()) {
            $event = $this->events->findOrThrow($values->id);

            if (($event->getFiles()->count() + 1) > $this->configParams->eventImgMaxCount) {
                $form->addError("Too much images were uploaded. Limit is " . $this->configParams->eventImgMaxCount);
                return;
            }

            // create folder if necessary
            if (!file_exists(getcwd() . $this->configParams->eventImgDir . $values->id . '/')) {
                mkdir(getcwd() . $this->configParams->eventImgDir . $values->id . '/');
            }

            if ($values->image->size > $this->configParams->eventImgFileSize) {
                $form->addError("Image is too big. Maximum is " . ($this->configParams->eventImgFileSize / 1000000) . "MB");
                return;
            }

            $file_ext = strtolower(pathinfo($values->image->name, PATHINFO_EXTENSION));
            while (true) {
                $targetFile = getcwd() . $this->configParams->eventImgDir . $values->id . '/' .
                        $this->stringHelpers->generateRandomString($this->configParams->eventImgNameLength) . '.' . $file_ext;
                if (!file_exists($targetFile)) {
                    break;
                }
            }

            $values->image->move($targetFile);

            $eventFile = new EventFile(
                $this->user,
                $event,
                $values->image->name,
                basename($targetFile)
            );
            $eventFile->modified($this->user);
            $this->eventFiles->persist($eventFile);
        } elseif (!$values->image->isImage()) {
            $form->addError('Uploaded file is not an image');
        } else {
            $form->addError('Badly uploaded image');
        }
    }
}
