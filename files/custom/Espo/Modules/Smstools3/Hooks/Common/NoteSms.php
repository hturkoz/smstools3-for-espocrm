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

namespace Espo\Modules\Smstools3\Hooks\Common;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Core\Utils\Log;
use Espo\Entities\User;

use Espo\Core\Sms\SmsSender;
use Espo\Core\Sms\SmsFactory;
use Espo\Entities\Sms;

class NoteSms
{    
    public static int $order = 5; 

    public function __construct( 
        private EntityManager $em, 
        private Log $log,
        private SmsSender $smsSender,
        private SmsFactory $smsFactory,
        private User $user
    ){}

    public function beforeSave(Entity $entity, array $options): void
    {
        if ( $entity->isNew() && substr($entity->get('post'), 0, 4) == strtolower('#sms') )
        { 
            $parentEntity = $this->em->getRDBRepository($entity->get('parentType'))
                    ->where([
                        'id' => $entity->get('parentId'),
                    ])
                    ->findOne();
            $toNumber = $parentEntity->get('phoneNumber');
            $body = str_replace("#sms ", "" , $entity->get('post'));

            $sms = $this->createSms($toNumber, $body);
            $this->smsSender->send($sms);
        }
    }

    private function createSms(string $phoneNumber, string $body ): Sms
    {
        $sms = $this->smsFactory->create();
        $sms->setFromNumber('04');
        $sms->addToNumber($phoneNumber);
        $sms->setBody($body);
        return $sms;
    }
}