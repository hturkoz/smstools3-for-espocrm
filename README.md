# smstools3 for espocrm
 Send and Receive sms with Smstools3 for linux with espocrm.

# important
 For expert only

# features
 - Send sms from any stream with '#sms hello espocrm' (key is '#sms')
 - Receive sms to a 'entity' (need re-working)

# todo before work
 - mount --bind /var/spool/sms /var/www/html/public_html/data/smsd
 - need create a job in admin section of espocrm 
 - add www-data to smsd group with 'sudo useradd –G smsd www-data'
