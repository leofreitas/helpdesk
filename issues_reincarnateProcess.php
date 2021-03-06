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

include "../../functions.php" ;
include "../../config.php" ;

include "./moduleFunctions.php" ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$URL = $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Help Desk/issues_view.php" ;

if (isset($_GET["issueID"])) {
      $issueID = $_GET["issueID"] ;
}

if ($issueID == "") {
    //Fail 3
    $URL = $URL . "&return=error1" ;
    header("Location: {$URL}");
}

$allowed = isPersonsIssue($connection2, $issueID, $_SESSION[$guid]["gibbonPersonID"]) || (relatedToIssue($connection2, $issueID, $_SESSION[$guid]["gibbonPersonID"]) && getPermissionValue($connection2, $_SESSION[$guid]["gibbonPersonID"], "reincarnateIssue"));

if (isActionAccessible($guid, $connection2, "/modules/Help Desk/issues_view.php") == false || !$allowed) {
    //Fail 0
    $URL = $URL . "&return=error0" ;
    header("Location: {$URL}");
} else {
    //Proceed!
    $status = "Pending";
    if (!hasTechnicianAssigned($connection2, $issueID)) {
        $status = "Unassigned";
    }

    //Write to database
    try {
        $gibbonModuleID = getModuleIDFromName($connection2, "Help Desk");
        if ($gibbonModuleID == null) {
            throw new PDOException("Invalid gibbonModuleID.");
        }

        $data = array("issueID" => $issueID, "status" => $status);
        $sql = "UPDATE helpDeskIssue SET status=:status WHERE issueID=:issueID" ;
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL = $URL . "&return=error2" ;
        header("Location: {$URL}");
    }

    $row = getIssue($connection2, $issueID);

    $message = "Issue #";
      $message .= $issueID;
      $message .= " (" . $row["issueName"] . ") has been reincarnated.";

      $personIDs = getPeopleInvolved($connection2, $issueID);

      foreach ($personIDs as $personID) {
        if ($personID != $_SESSION[$guid]["gibbonPersonID"]) {
            setNotification($connection2, $guid, $personID, $message, "Help Desk", "/index.php?q=/modules/Help Desk/issues_discussView.php&issueID=" . $issueID);
        } 
      }

    $array = array("issueID"=>$issueID);

    if (isTechnician($connection2, $_SESSION[$guid]["gibbonPersonID"])) {
        $array['technicianID'] = getTechnicianID($connection2, $_SESSION[$guid]["gibbonPersonID"]);
    }

    setLog($connection2, $_SESSION[$guid]["gibbonSchoolYearID"], $gibbonModuleID, $_SESSION[$guid]["gibbonPersonID"], "Issue Reincarnated", $array, null);

    //Success 0
    $URL=$URL . "&return=success0" ;
    header("Location: {$URL}");
}
?>
