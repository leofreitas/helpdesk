<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Forms\Form;

// get session object
$session = $container->get('session');

include __DIR__ . '/moduleFunctions.php';

$issueID = $_GET["issueID"] ?? '';

if (isActionAccessible($guid, $connection2, "/modules/Help Desk/issues_view.php") == false || getPermissionValue($connection2, $session->get('gibbonPersonID'), "fullAccess")) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    if (!isPersonsIssue($connection2, $issueID, $session->get('gibbonPersonID'))){
        $page->addError(__('No issue selected.'));
    }
    else {
        //Proceed!
        $page->breadcrumbs->add(__("Discuss Issue"), 'issues_discussView.php', ['issueID' => $issueID]);
        $page->breadcrumbs->add(__('Edit Privacy'));
        
        $options = array("Everyone" => __("Everyone"), "Related"=>__("Related"), "Owner"=>__("Owner"), "No one"=>__("No one"));

        $data = array("issueID" => $issueID);
        $sql = "SELECT privacySetting FROM helpDeskIssue WHERE issueID=:issueID" ;
        $result = $connection2->prepare($sql);
        $result->execute($data);
        $row = $result->fetch() ;
        $privacySetting = $row['privacySetting'];

        $form = Form::create('action', $session->get('absoluteURL').'/modules/'.$session->get('module').'/issues_discussEditProcess.php?issueID='.$issueID);
     
        $form->addRow()->addHeading(__('Privacy Setting'));

            $row = $form->addRow();
                    $row->addLabel('privacySetting', __('Privacy Setting'));
                    $row->addSelect('privacySetting')->fromArray($options)->selected($privacySetting)->required();
 
            $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

            echo $form->getOutput();
    }
}
?>