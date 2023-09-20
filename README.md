# smstools3 for espocrm
 Send and Receive sms with [smstools3](http://smstools3.kekekasvi.com) for linux with [EspoCRM](https://github.com/espocrm/espocrm) .

# importants
 For expert only, this is a base for programmers

# features
 - Send sms from any stream with '#sms hello espocrm' (key is '#sms')
 - Receive sms to a 'entity' (need adapted actually)

# todo before work
 - path in integration setting : data/smsd 
 - mount --bind /var/spool/sms /var/www/html/public_html/data/smsd
 - need create a job in admin section of [EspoCRM](https://github.com/espocrm/espocrm) 
 - add www-data to smsd group with 'sudo useradd –G smsd www-data'

# issues
 - certainly somes char encoding
 - ..

# requirements
 - [SMS Server Tools 3](http://smstools3.kekekasvi.com) is a SMS Gateway software which can send and receive short messages through GSM modems and mobile phones.

# license
 - [see EspoCRM licence](https://github.com/espocrm/espocrm)
 - [EspoCRM](https://github.com/espocrm/espocrm) is published under the GNU GPLv3
