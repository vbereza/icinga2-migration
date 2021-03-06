<?php
// {{{ICINGA_LICENSE_HEADER}}}
/**
 * This file is part of Icinga Web 2.
 *
 * Icinga Web 2 - Head for multiple monitoring backends.
 * Copyright (C) 2014 Icinga Development Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @copyright  2013 Icinga Development Team <info@icinga.org>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPL, version 2
 * @author     Icinga Development Team <info@icinga.org>
 *
 */
// {{{ICINGA_LICENSE_HEADER}}}

namespace Icinga\Module\Conftool\Clicommands;

use Icinga\Cli\Command;
use Icinga\Module\Conftool\Icinga\IcingaConfig;
use Icinga\Module\Conftool\Icinga2\Icinga2ObjectDefinition;

class MigrateCommand extends Command
{
    public function v1Action()
    {
        $start = microtime(true);

        printf("//---------------------------------------------------\n");
        printf("//Migrate Icinga 1.x configuration to Icinga 2 format\n");
        printf("//Start time: ".date("Y-m-d H:i:s")."\n");
        printf("//---------------------------------------------------\n");

        //parse 1.x objects
        $configfile = $this->params->shift();
        $config = IcingaConfig::parse($configfile);

        //dump default templates for new objects
        Icinga2ObjectDefinition::dumpDefaultTemplates();

        //migrate all objects to 2.x
        printf("//MIGRATE COMMANDS -- BEGIN\n");
        foreach ($config->getDefinitions('command') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE COMMANDS -- END\n");

        printf("//MIGRATE HOSTS -- BEGIN\n");
        foreach ($config->getDefinitions('host') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();

            //direct host->service relation
            if (count($object->getServices()) > 0) {
                printf("//---- MIGRATE HOST SERVICES -- BEGIN\n");
                foreach($object->getServices() as $service) {
                    $service->host_name = $object; //force relation
                    Icinga2ObjectDefinition::fromIcingaObjectDefinition($service, $config)->dump();
                }
                printf("//---- MIGRATE HOST SERVICES -- END\n");
            }
        }
        printf("//MIGRATE HOSTS -- END\n");

        printf("//MIGRATE SERVICE -- BEGIN\n");
        //TODO only templates should dumped?
        foreach ($config->getDefinitions('service') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE SERVICE -- END\n");

        printf("//MIGRATE CONTACTS (USERS) -- BEGIN\n");
        foreach ($config->getDefinitions('contact') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE CONTACTS (USERS) -- END\n");

        printf("//MIGRATE HOSTGROUPS -- BEGIN\n");
        foreach ($config->getDefinitions('hostgroup') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();

            //indirect hostgroup->service relation
            if (count($object->getServices()) > 0) {
                printf("//---- MIGRATE HOSTGROUP SERVICES -- BEGIN\n");
                foreach($object->getServices() as $service) {
                    $service->hostgroup_name = $object; //force relation
                    Icinga2ObjectDefinition::fromIcingaObjectDefinition($service, $config)->dump();
                }
                printf("//---- MIGRATE HOSTGROUP SERVICES -- END\n");
            }
        }
        printf("//MIGRATE HOSTGROUPS -- END\n");

        printf("//MIGRATE SERVICEGROUPS -- BEGIN\n");
        foreach ($config->getDefinitions('servicegroup') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE SERVICEGROUPS -- END\n");

        printf("//MIGRATE CONTACTGROUPS (USERGROUPS) -- BEGIN\n");
        foreach ($config->getDefinitions('contactgroup') as $object) { // TODO: Find a better way than hardcoded
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE CONTACTGROUPS (USERGROUPS) -- END\n");

        printf("//MIGRATE TIMEPERIODS -- BEGIN\n");
        foreach ($config->getDefinitions('timeperiod') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE TIMEPERIODS -- END\n");

        printf("//MIGRATE HOST DEPENDENCIES -- BEGIN\n");
        foreach ($config->getDefinitions('hostdependency') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE HOST DEPENDENCIES -- END\n");

        printf("//MIGRATE SERVICE DEPENDENCIES -- BEGIN\n");
        foreach ($config->getDefinitions('servicedependency') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE SERVICE DEPENDENCIES -- END\n");

        printf("//MIGRATE HOST ESCALATION -- BEGIN\n");
        foreach ($config->getDefinitions('hostescalation') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE HOST ESCALATION -- END\n");

        printf("//MIGRATE SERVICE ESCALATION -- BEGIN\n");
        foreach ($config->getDefinitions('serviceescalation') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object, $config)->dump();
        }
        printf("//MIGRATE SERVICE ESCALATION -- END\n");

        $end = microtime(true);
        $runtime = $end - $start;

        printf("//---------------------------------------------------\n");
        printf("//FINISHED :-)\n");
        printf("//End time: " . date("Y-m-d H:i:s") . "\n");
        printf("//Runtime: " . (float)$runtime . "\n");
        printf("//---------------------------------------------------\n");

    }
}
