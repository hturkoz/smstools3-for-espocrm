<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2023 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/
 
namespace Espo\Modules\Smstools3\Jobs;;

use Espo\Core\Job\JobDataLess;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Entities\Integration;

class Smstools3Incoming implements JobDataLess
{
    private const BASE_PATH = 'data/smsd';

    public function __construct( 
        private EntityManager $em, 
        private Log $log,
        private FileManager $fileManager,
    ){}

    public function run() : void
    {
        $integration = $this->getIntegrationEntity();

        $basePath = rtrim(
            $integration->get('smstools3BasePath') ??
            self::BASE_PATH
        );

        $path = $basePath . '/incoming/';
        $fileList = $this->fileManager->getFileList($path, false, '', true);

        foreach ($fileList as $file) {

            $message = $from = $fromSmsc = $sent = $received = $subject = $alphabet = $udh = '';

            $fileData = $this->fileManager->getContents($path .$file);

            $lines = explode("\n", $fileData);

            $body = array_pop($lines);  // last line containt body text.
            $empty = array_pop($lines); // empty line, no need.

            foreach($lines as $line){
                if ($line){
                    $splitedLine = explode(": ", $line  );
                    switch( $splitedLine[0] ){
                        case "From": 
                            $from = trim($splitedLine[1]);
                            break;
                        case 'From_SMSC':
                            $fromSmsc = trim($splitedLine[1]);
                            break;
                        case 'Sent':
                        /*
                            $myDateTime = \DateTime::createFromFormat('d-m-y H:m:s', trim($splitedLine[1]));
                            $sent = $myDateTime->format('Y-m-d H:m:s');
                        */
                            break;
                        case 'Received':
                        /*
                            $myDateTime = \DateTime::createFromFormat('d-m-y H:m:s', trim($splitedLine[1]));
                            $received = $myDateTime->format('Y-m-d H:m:s');
                        */
                            break;
                        case 'Subject':
                             $subject = trim($splitedLine[1]);
                            break;
                        case 'Alphabet': // important to find other encoding
                            $alphabet = trim($splitedLine[1]);
                            break;
                        case 'UDH':
                             $udh = trim($splitedLine[1]);
                            break;
                        default : 
                    }
                }
            }

            $sms = $this->em->createEntity('Sms', [
                'name' => $from,
                'type' => 'From',
                'body' => $body,
                'description' => $fileData,
                'assignedUserId' => 'system',
            ]);

            if ($sms){
                $this->fileManager->removeFile($file, $path);
            }
        }
    }

    private function getIntegrationEntity(): Integration
    {
        $entity = $this->em->getEntity(Integration::ENTITY_TYPE, 'Smstools3');

        if (!$entity || !$entity->get('enabled')) {
            throw new Error("Smstools3 for linux integration is not enabled");
        }

        return $entity;
    }
}
