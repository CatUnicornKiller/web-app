<?php

namespace App\Forms;

use App;
use Nette;
use Nette\Application\UI\Form;
use App\Model\Entity\User;
use App\Model\Entity\CpTask;
use App\Model\Entity\DefaultCpTask;
use App\Users\UserManager;
use App\Model\Repository\CpTasks;
use App\Model\Repository\CpAssignedAfs;
use App\Model\Repository\DefaultCpTasks;

/**
 * Class containing factory methods for forms mainly concerning tasks.
 * Alongside factories there can also be success callbacks.
 */
class TasksFormsFactory
{
    use Nette\SmartObject;

    /** @var User */
    private $user;
    /** @var CpTasks */
    private $cpTasks;
    /** @var DefaultCpTasks */
    private $defaultCpTasks;
    /** @var CpAssignedAfs */
    private $cpAssignedAfs;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param CpTasks $cpTasks
     * @param CpAssignedAfs $cpAssignedAfs
     * @param DefaultCpTasks $defaultCpTasks
     */
    public function __construct(
        UserManager $userManager,
        CpTasks $cpTasks,
        CpAssignedAfs $cpAssignedAfs,
        DefaultCpTasks $defaultCpTasks
    ) {

        $this->user = $userManager->getCurrentUser();
        $this->cpTasks = $cpTasks;
        $this->cpAssignedAfs = $cpAssignedAfs;
        $this->defaultCpTasks = $defaultCpTasks;
    }

    /**
     * Create change state of the user tasks form.
     * @param int $id user identification
     * @param array $tasks
     * @return \App\Forms\MyForm
     */
    public function createChangeTasksStatesForm($id, $tasks)
    {
        $form = new MyForm;
        $form->addHidden('id', $id);
        $ct = $form->addContainer('cpTasks');
        foreach ($tasks as $task) {
            $ct->addCheckbox($task->id, $task->cpTasksDescription)
                    ->setDefaultValue($task->completed);
        }
        $form->addSubmit('send', 'Save');
        $form->onSuccess[] = array($this, 'changeTasksStatesFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the change tasks state form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function changeTasksStatesFormSucceeded(MyForm $form, $values)
    {
        foreach ($values->cpTasks as $key => $val) {
            $task = $this->cpTasks->findOrThrow($key);
            $task->completed = $val;
            $task->modified($this->user);
            $this->cpTasks->flush();
        }
    }

    /**
     * Create base form for the task editation.
     * @param \App\Forms\MyForm $form
     */
    private function createBasicTaskForm(MyForm $form)
    {
        $form->addText('cpTasksDescription', 'Task Description')
                ->setRequired('Task Description is mandatory')
                ->addRule(Form::MAX_LENGTH, 'Task Description is too long', 1000)
                ->setAttribute('length', 1000);
        $form->addTextArea('cpTasksNote', 'Task Note')
                ->setRequired('Task Note is required')
                ->addRule(Form::MAX_LENGTH, 'Task Note is too long', 1000)
                ->setAttribute('length', 1000);
    }

    /**
     * Create add default task form.
     * @return \App\Forms\MyForm
     */
    public function createAddDefaultTaskForm()
    {
        $form = new MyForm;
        $this->createBasicTaskForm($form);
        $form->addSubmit('send', 'Add Task');
        $form->onSuccess[] = array($this, 'addDefaultTaskFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the add default task form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function addDefaultTaskFormSucceeded(MyForm $form, $values)
    {
        $task = new DefaultCpTask($values->cpTasksDescription, $values->cpTasksNote);
        $this->defaultCpTasks->persist($task);
    }

    /**
     * Create edit default task form.
     * @param CpTask $task
     * @return \App\Forms\MyForm
     */
    public function createEditDefaultTaskForm($task)
    {
        $form = new MyForm;
        $form->addHidden('id');
        $this->createBasicTaskForm($form);

        $form->setDefaults($task->toArray());

        $form->addSubmit('send', 'Edit Task');
        $form->onSuccess[] = array($this, 'editDefaultTaskFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the edit default task form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function editDefaultTaskFormSucceeded(MyForm $form, $values)
    {
        $task = $this->defaultCpTasks->findOrThrow($values->id);
        $task->cpTasksDescription = $values->cpTasksDescription;
        $task->cpTasksNote = $values->cpTasksNote;
        $this->defaultCpTasks->flush();
    }

    /**
     * Create edit user tasks form.
     * @param int $id user identification
     * @param array $tasks
     * @return \App\Forms\MyForm
     */
    public function createEditTasksForm($id, $tasks)
    {
        $form = new MyForm;
        $form->addHidden('id')
                ->setDefaultValue($id);

        $descs = $form->addContainer('cpTasksDescription');
        $notes = $form->addContainer('cpTasksNote');
        $sort_order = $form->addContainer('sortOrder');
        foreach ($tasks as $task) {
            $descs->addTextArea($task->id, 'Task Description')
                    ->setRequired('Task Description is required')
                    ->setDefaultValue($task->cpTasksDescription)
                    ->addRule(Form::MAX_LENGTH, 'Task Description is too long', 1000)
                    ->setAttribute('length', 1000);
            $notes->addTextArea($task->id, 'Task Note')
                    ->setRequired('Task Note is required')
                    ->setDefaultValue($task->cpTasksNote)
                    ->addRule(Form::MAX_LENGTH, 'Task Note is too long', 1000)
                    ->setAttribute('length', 1000);
            $sort_order->addText($task->id, 'Sort Order')
                    ->setType('number')
                    ->setDefaultValue($task->sortOrder)
                    ->setAttribute('class', 'event_points');
        }

        $form->addSubmit('send', 'Edit Tasks');
        $form->onSuccess[] = array($this, 'editTasksFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the edit user tasks form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function editTasksFormSucceeded(MyForm $form, $values)
    {
        foreach ($values->cpTasksDescription as $key => $desc) {
            $note = $values->cpTasksNote[$key];
            $sortOrder = $values->sortOrder[$key];

            $task = $this->cpTasks->findOrThrow($key);
            $task->cpTasksDescription = $desc;
            $task->cpTasksNote = $note;
            $task->sortOrder = $sortOrder;
            $task->modified($this->user);
            $this->cpTasks->flush();
        }
    }

    /**
     * Create add user tasks form.
     * @param int $id user identification
     * @param array $defaultTasks
     * @return \App\Forms\MyForm
     */
    public function createAddTasksForm($id, $defaultTasks)
    {
        $form = new MyForm;

        $form->addHidden('id')
                ->setDefaultValue($id);

        $cpTasks = $form->addDynamic('cpTasks', function (App\Forms\MyContainer $cpTasks) {
            $cpTasks->addCheckbox('cpTasksSelection')
                    ->setDefaultValue(1);
            $cpTasks->addTextArea('cpTasksDescription', 'Task Description')
                    ->setRequired('Task Description is required')
                    ->addRule(Form::MAX_LENGTH, 'Task Description is too long', 1000)
                    ->setAttribute('length', 1000);
            $cpTasks->addTextArea('cpTasksNote', 'Task Note')
                    ->setRequired('Task Note is required')
                    ->addRule(Form::MAX_LENGTH, 'Task Note is too long', 1000)
                    ->setAttribute('length', 1000);
        });
        $cpTasks->containerClass = 'App\Forms\MyContainer';

        $defTmpTasks = array();
        foreach ($defaultTasks as $task) {
            $tmp = array();
            $tmp['cpTasksSelection'] = 0;
            $tmp['cpTasksDescription'] = $task->cpTasksDescription;
            $tmp['cpTasksNote'] = $task->cpTasksNote;
            $defTmpTasks[] = $tmp;
        }
        $cpTasks->setValues($defTmpTasks);
        $cpTasks->createOne();

        $cpTasks->addSubmit('add', 'Add new task')
                ->addCreateOnClick(true);

        $form->addSubmit('send', 'Add Tasks');
        $form->onSuccess[] = array($this, 'addTasksFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the add user tasks form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function addTasksFormSucceeded(MyForm $form, $values)
    {
        $assigned = $this->cpAssignedAfs->findOrThrow($values->id);

        foreach ($values->cpTasks as $task) {
            if ($task->cpTasksSelection == false) {
                continue;
            }

            $task = new CpTask(
                $assigned,
                $assigned->afNumber,
                $task->cpTasksDescription,
                $task->cpTasksNote
            );
            $task->modified($this->user);
            $this->cpTasks->persist($task);
        }
    }
}
