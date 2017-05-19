<?php

namespace App\Forms;

use App;
use Nette;
use App\Model\Entity\User;
use App\Model\Repository\Users;
use App\Model\Repository\Faculties;
use App\Users\UserManager;

/**
 * Class containing factory methods for forms mainly concerning incomings.
 * Alongside factories there can also be success callbacks.
 */
class IncomingsFormsFactory extends Nette\Object
{
    /** @var User */
    private $user;
    /** @var Users */
    private $users;
    /** @var Faculties */
    private $faculties;
    /** @var App\Users\RolesManager */
    private $rolesManager;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param Users $users
     * @param Faculties $faculties
     * @param App\Users\RolesManager $rolesManager
     */
    public function __construct(
        UserManager $userManager,
        Users $users,
        Faculties $faculties,
        App\Users\RolesManager $rolesManager
    ) {

        $this->user = $userManager->getCurrentUser();
        $this->users = $users;
        $this->rolesManager = $rolesManager;
        $this->faculties = $faculties;
    }

    /**
     * Create change incoming role form.
     * @param int $id incoming identification
     * @param array $rolesDesc possible roles of incomings
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
     * Success callback for the change incomings role form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function changeRoleFormSucceeded(MyForm $form, $values)
    {
        $usr = $this->users->findIncomingOrThrow($values->id);
        $usr->roleModified($this->user, $usr->role, $values->role);
        $usr->role = $values->role;
        $this->users->flush();
    }

    /**
     * Create filtering form for the incomings list. Return simple form with
     * GET method set.
     * @param int $facultyId faculty identification
     * @param string $priv privileges identification
     * @return \App\Forms\MySimpleForm
     */
    public function createFilterIncomingsForm($facultyId, $priv)
    {
        $faculties = array();
        foreach ($this->faculties->findAll() as $faculty) {
            $faculties[$faculty->id] = $faculty->facultyName;
        }

        $form = new MySimpleForm;
        $form->setMethod('get');

        $form->addSelect('faculty', 'Faculty', $faculties)
                ->setPrompt('All faculties');
        $form->addSelect('privileges', 'Privileges', $this->rolesManager->getIncomingRoles())
                ->setPrompt('All privileges');
        $form->addSubmit('send', 'Filter');


        try {
            $form->setDefaults(array( 'faculty' => $facultyId, 'privileges' => $priv ));
        } catch (\Exception $e) {
        }

        return $form;
    }
}
