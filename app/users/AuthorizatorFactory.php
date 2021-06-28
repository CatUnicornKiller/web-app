<?php

namespace App\Users;

use Nette\Security\Authorizator;
use Nette\Security\Permission;

/**
 * Nette authorizator service factory.
 */
class AuthorizatorFactory
{
    /**
     * Creates and returns authorizator for the application. Roles, resources
     * and permissions are defined here and not in the database!
     * @return Authorizator
     */
    public function create(): Authorizator
    {
        $permission = new Permission();

        /* list of user roles */
        $permission->addRole('guest');
        $permission->addRole('nobody');
        $permission->addRole('cp', 'nobody');
        $permission->addRole('leo_assist', 'cp');
        $permission->addRole('lore_assist', 'leo_assist');
        $permission->addRole('leo', 'leo_assist');
        $permission->addRole('lore', 'leo');
        $permission->addRole('neo_assist', 'leo');
        $permission->addRole('neo', 'neo_assist');
        $permission->addRole('nore', 'neo');
        $permission->addRole('treasurer', 'nore');
        $permission->addRole('admin', 'treasurer');

        /* incomings user roles */
        $permission->addRole('incoming');

        /* list of resources */
        $permission->addResource('IncomingsMenu');
        $permission->addResource('ContactPersonsMenu');
        $permission->addResource('RegisteredIncomingsMenu');
        $permission->addResource('LeosNeosAssistantsMenu');
        $permission->addResource('LeosNeosMenu');
        $permission->addResource('NeosAssistantsMenu');
        $permission->addResource('UsersList');
        $permission->addResource('IncomingsList');
        $permission->addResource('Users');
        $permission->addResource('Incomings');
        $permission->addResource('IfmsaRemote');
        $permission->addResource('MyIfmsaCredentials');
        $permission->addResource('CPIntroduction');
        $permission->addResource('CPProfile');
        $permission->addResource('IncomingsGeneralInformation');
        $permission->addResource('IncomingsIfmsaHierarchy');
        $permission->addResource('Event');
        $permission->addResource('Events');
        $permission->addResource('AssignAf');
        $permission->addResource('Faculties');
        $permission->addResource('Tasks');
        $permission->addResource('DefaultTasks');
        $permission->addResource('News');
        $permission->addResource('IncomingsFacultyInformation');
        $permission->addResource('IncomingsFacultyInformationList');
        $permission->addResource('NewbyInfo');
        $permission->addResource('EventTransaction');
        $permission->addResource('Feedback');
        $permission->addResource('Showroom');
        $permission->addResource('AllEvents');
        $permission->addResource('CUKStats');
        $permission->addResource('UploadArticleImages');

        /* list of permission rules */
        $permission->allow('nobody', array('NewbyInfo'), 'view');

        $permission->allow('cp', array('ContactPersonsMenu', 'CPIntroduction',
            'Incomings', 'CPProfile', 'Event',
            'Tasks', 'News', 'CUKStats'), 'view');
        $permission->allow('cp', array('Event'), 'edit');
        $permission->allow('cp', array('Event'), 'add');
        $permission->allow('cp', array('Event'), 'delete');

        $permission->allow('leo_assist', array(
            'RegisteredIncomingsMenu', 'LeosNeosAssistantsMenu', 'AssignAf',
            'IfmsaRemote', 'IncomingsList', 'IncomingsGeneralInformation',
            'Users', 'UsersList', 'IncomingsFacultyInformation',
            'UploadArticleImages', 'IncomingsIfmsaHierarchy'), 'view');
        $permission->allow('leo_assist', array('Users', 'Incomings'), 'changeRole');
        $permission->allow('leo_assist', array('CPIntroduction', 'Tasks',
            'IncomingsGeneralInformation', 'IncomingsFacultyInformation'), 'edit');

        $permission->allow('leo', array('LeosNeosMenu'), 'view');
        $permission->allow('leo', array('News'), 'add');
        $permission->allow('leo', array('Users', 'Incomings', 'News'), 'delete');
        $permission->allow('leo', array('Users'), 'changeIfmsa');
        $permission->allow('leo', array('MyIfmsaCredentials', 'Incomings', 'News'), 'edit');

        $permission->allow(
            'neo_assist',
            array('NeosAssistantsMenu', 'DefaultTasks',
            'IncomingsFacultyInformationList', 'EventTransaction', 'AllEvents'),
            'view'
        );
        $permission->allow('neo_assist', array('DefaultTasks'), 'edit');
        $permission->allow('neo_assist', array('Events'), 'generateTable');
        $permission->allow('neo_assist', array('EventTransaction'), 'reverse');
        $permission->allow('neo_assist', array('Feedback', 'Showroom', 'Faculties'), 'management');

        /* list of incomings rules */
        $permission->allow('incoming', array('IncomingsGeneralInformation',
            'IncomingsMenu', 'Incomings', 'Event', 'IncomingsIfmsaHierarchy',
            'IncomingsFacultyInformation', 'News', 'CUKStats'), 'view');
        $permission->allow('incoming', array('Event'), 'sign');

        /* Deny rules */
        $permission->deny('cp', 'NewbyInfo', 'view');

        /* guest cant do anything */
        $permission->deny('guest', Permission::ALL, Permission::ALL);

        return $permission;
    }
}
