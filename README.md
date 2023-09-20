# smstools3 for espocrm
 Send and Receive sms with Smstools3 for linux with EspoCRM.

# importants
 For expert only, this is a base for programmers

# features
 - Send sms from any stream with '#sms hello espocrm' (key is '#sms')
 - Receive sms to a 'entity' (need adapted actually)

# todo before work
 - path in integration setting : data/smsd 
 - mount --bind /var/spool/sms /var/www/html/public_html/data/smsd
 - need create a job in admin section of EspoCRM 
 - add www-data to smsd group with 'sudo useradd –G smsd www-data'

# issues
 - certainly somes char encoding  

# requirements
 - a linux with smstools3 installed and working

# license
 - [see EspoCRM licence](https://github.com/espocrm/espocrm)
 - EspoCRM is published under the GNU GPLv3
