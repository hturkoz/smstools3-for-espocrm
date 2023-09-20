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

namespace Espo\Modules\Smstools3\Smstools3;

use Espo\Core\Sms\Sender;
use Espo\Core\Sms\Sms;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Json;
use Espo\Core\Exceptions\Error;

use Espo\ORM\EntityManager;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Util;

use Espo\Entities\Integration;

use Throwable;

class Smstools3Sender implements Sender
{
    private const BASE_PATH = 'data/smsd';

    private const TIMEOUT = 10;

    public function __construct(
        private Config $config, 
        private EntityManager $em, 
        private Log $log,
        private FileManager $fileManager,
    ){}

    public function send(Sms $sms): void
    {
        $toNumberList = $sms->getToNumberList();

        if (!count($toNumberList)) {
            throw new Error("No recipient phone number.");
        }

        foreach ($toNumberList as $number) {
            $this->sendToNumber($sms, $number);
        }
    }

    private function sendToNumber(Sms $sms, string $toNumber): void
    {
        $integration = $this->getIntegrationEntity();

        $basePath = rtrim(
            $integration->get('smstools3BasePath') ??
            self::BASE_PATH
        );

        $timeout = $this->config->get('smstools3SmsSendTimeout') ?? self::TIMEOUT;

        if (!$basePath) {
            throw new Error("No Smstool3 Base Path outgoing Folder.");
        }


        if (!$toNumber) {
            throw new Error("No recipient phone number.");
        }

        $basePath = $basePath . '/outgoing/';
        $body = $sms->getBody();
        $smsFile = $this->buildFile($toNumber, $body);
        $this->process( $basePath, $smsFile );
    }

    private function buildFile(string $toNumber, string $body): string
    {
        $toNumber = self::formatNumber($toNumber);
        return "To: {$toNumber}\nAlphabet: UTF-8\n\n{$body}";
    }

    private static function formatNumber(string $number): string
    {
        return preg_replace('/[^0-9]/', '', $number);
    }

    private function process( string $basePath, string $smsFile ): void
    {
        // mount --bind /var/spool/sms /var/www/html/public_html/data/smsd
        $result = $this->fileManager->putContents($basePath .Util::generateId(), $smsFile);

        if (!$result) {
            $this->log->error("Smstools3 SMS sending error. result: " .$result);
        }
    }

    private function getIntegrationEntity(): Integration
    {
        $entity = $this->em
            ->getEntity(Integration::ENTITY_TYPE, 'Smstools3');

        if (!$entity || !$entity->get('enabled')) {
            throw new Error("Smstools3 integration is not enabled");
        }

        return $entity;
    }
}
