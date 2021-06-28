<?php

namespace App\Presenters;

use App;
use App\Model\Repository\IfmsaPersons;
use App\Model\Repository\CpTasks;
use App\Model\Repository\DefaultCpTasks;
use App\Model\Repository\CpAssignedAfs;

/**
 * Tasks presenter.
 */
class TasksPresenter extends BasePresenter
{
    /**
     * @var App\Forms\TasksFormsFactory
     * @inject
     */
    public $tasksFormsFactory;
    /**
     * @var IfmsaPersons
     * @inject
     */
    public $ifmsaPersons;
    /**
     * @var CpTasks
     * @inject
     */
    public $cpTasks;
    /**
     * @var DefaultCpTasks
     * @inject
     */
    public $defaultCpTasks;
    /**
     * @var CpAssignedAfs
     * @inject
     */
    public $cpAssignedAfs;

    protected function createChangeTasksStatesForm($id, $tasks)
    {
        $form = $this->tasksFormsFactory->createChangeTasksStatesForm($id, $tasks);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Tasks states successfully changed');
            $form->presenter->redirect('Tasks:Detail', $values->id);
        };
        return $form;
    }

    protected function createComponentAddDefaultTaskForm()
    {
        $form = $this->tasksFormsFactory->createAddDefaultTaskForm();
        $form->onSuccess[] = function (App\Forms\MyForm $form) {
            $form->presenter->flashMessage('Default task successfully added');
            $form->presenter->redirect('Tasks:defaultTasks');
        };
        return $form;
    }

    protected function createEditDefaultTaskForm($task)
    {
        $form = $this->tasksFormsFactory->createEditDefaultTaskForm($task);
        $form->onSuccess[] = function (App\Forms\MyForm $form) {
            $form->presenter->flashMessage('Default task successfully edited');
            $form->presenter->redirect('Tasks:defaultTasks');
        };
        return $form;
    }

    protected function createEditTasksForm($id, $tasks)
    {
        $form = $this->tasksFormsFactory->createEditTasksForm($id, $tasks);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Tasks were successfully edited');
            $form->presenter->redirect('Tasks:detail', $values->id);
        };
        return $form;
    }

    protected function createAddTasksForm($id, $defaultTasks)
    {
        $form = $this->tasksFormsFactory->createAddTasksForm($id, $defaultTasks);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Tasks were successfully added');
            $form->presenter->redirect('Tasks:detail', $values->id);
        };
        return $form;
    }

    public function actionOverview()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Tasks", "view")) {
            $this->error('Access Denied');
        }

        $assigned = $this->currentUser->getAssignedIncomings();
        $personTasks = array();
        foreach ($assigned as $assign) {
            $ifmsaPerson = $this->ifmsaPersons->findByAfNumber($assign->afNumber);
            $personTasks[$assign->afNumber] = array($ifmsaPerson, $assign);
        }
        $this->template->personTasks = $personTasks;
    }

    public function actionDetail($id)
    {
        $assignedAf = $this->cpAssignedAfs->findOrThrow($id);
        $userDetail = $assignedAf->getUser();
        if (!$this->isLoggedIn() || !$userDetail->isOfficer() ||
                !$this->user->isAllowed("Tasks", "view") ||
                !$this->myAuthorizator->isAllowedTasks('view', $userDetail->getFaculty()->getId())) {
            $this->error('Access Denied');
        }

        $tasks = $assignedAf->getTasks();
        $ifmsaPerson = $this->ifmsaPersons->findByAfNumber($assignedAf->getAfNumber());

        $this['changeTasksStatesForm'] = $this->createChangeTasksStatesForm($id, $tasks);

        $this->template->assignedAf = $assignedAf;
        $this->template->afNumber = $assignedAf->getAfNumber();
        $this->template->userDetail = $userDetail;
        $this->template->tasks = $tasks;
        $this->template->ifmsaPerson = $ifmsaPerson;
        $this->template->canEdit = $this->myAuthorizator->isAllowedTasks('edit', $userDetail->getFaculty()->getId());
        $this->template->canAdd = $this->myAuthorizator->isAllowedTasks('add', $userDetail->getFaculty()->getId());
    }

    public function actionDeleteTask($id)
    {
        $task = $this->cpTasks->findOrThrow($id);
        if (!$this->isLoggedIn() || !$this->user->isAllowed("Tasks", "edit") ||
                !$this->myAuthorizator->isAllowedTasks('delete', $task->getCpAssignedAf()->getUser()->getFaculty()->getId())) {
            $this->error('Access denied');
        }

        $task->modified($this->currentUser);
        $task->delete();
        $this->cpTasks->flush();

        $this->flashMessage('Task successfully deleted');
        $this->redirect('Tasks:detail', $task->getCpAssignedAf()->getId());
    }

    public function actionEditTasks($id)
    {
        $assignedAf = $this->cpAssignedAfs->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Tasks", "edit") ||
                !$this->myAuthorizator->isAllowedTasks('edit', $assignedAf->getUser()->getFaculty()->getId())) {
            $this->error('Access Denied');
        }

        $tasks = $assignedAf->getTasks();
        $this['editTasksForm'] = $this->createEditTasksForm($id, $tasks);

        $this->template->tasks = $tasks;
    }

    public function actionAddTasks($id)
    {
        $assignedAf = $this->cpAssignedAfs->findOrThrow($id);
        $userDetail = $assignedAf->getUser();
        if (!$this->isLoggedIn() || !$userDetail ||
                !$this->user->isAllowed("Tasks", "edit") ||
                !$this->myAuthorizator->isAllowedTasks('add', $userDetail->getFaculty()->getId())) {
            $this->error('Access Denied');
        }

        $defaultTasks = $this->defaultCpTasks->findAll();
        $this['addTasksForm'] = $this->createAddTasksForm($id, $defaultTasks);
    }

    public function actionDefaultTasks()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("DefaultTasks", "view")) {
            $this->error('Access Denied');
        }

        $this->template->defaultTasks = $this->defaultCpTasks->findAll();
    }

    public function actionAddDefaultTask()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("DefaultTasks", "edit")) {
            $this->error('Access Denied');
        }
    }

    public function actionEditDefaultTask($id)
    {
        $task = $this->defaultCpTasks->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("DefaultTasks", "edit")) {
            $this->error('Access Denied');
        }

        $this['editDefaultTaskForm'] = $this->createEditDefaultTaskForm($task);
    }

    public function actionDeleteDefaultTask($id)
    {
        $task = $this->defaultCpTasks->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("DefaultTasks", "edit")) {
            $this->error('Access Denied');
        }

        $this->defaultCpTasks->remove($task);

        $this->flashMessage('Default task successfully deleted');
        $this->redirect('Tasks:defaultTasks');
    }
}
