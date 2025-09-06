<?php

$labels = array();
$labels['nosignature'] = 'Χωρίς πληροφορίες υπογραφής';
$labels['noauthresults'] = 'Βρέθηκε υπογραφή αλλά όχι κεφαλίδα αποτελεσμάτων πιστοποίησης. Πιθανό πρόβλημα με την ρύθμιση του διακομιστή;';
$labels['signaturepass'] = 'Έγκυρη(ες) υπογραφή(ές) από τον τομέα του αποστολέα. Επαλήθευση από';
$labels['partialpass'] = 'Μερικές υπογραφές είναι άκυρες αλλά τουλάχιστο μία είναι έγκυρη για τον τομέα του αποστολέα. Επαλήθευση από';
$labels['thirdparty'] = 'Υπάρχει υπογραφή από τρίτη αρχή αλλά για διαφορετικό τομέα από ότι η διεύθυνση αποστολέα. Επικυρωμένο για';
$labels['temporaryinvalid'] = 'Η υπογραφή είναι άκυρη (προσωρινά)! Επικυρωμένη από';
$labels['invalidsignature'] = 'Η υπογραφή είναι άκυρη! Επικύρωση από';

$labels['for'] = 'για';
$labels['by'] = 'από';

$labels['section_title']  = 'Κατάσταση πιστοποίησης';
$labels['column_title']  = 'Αποτέλεσμα πιστοποίησης';

$labels['title_enable_column']  = 'Ενεργοποίηση στήλης';
$labels['label_enable_column']  = 'Ενεργοποίηση στήλης αποτελέσματος πιστοποίησης στην λίστα μηνυμάτων';
$labels['label_fallback_verifier'] = 'Χρήση εσωτερικού επαληθευτή DKIM (αν η υπογραφή βρέθηκε αλλά όχι και η κεφαλίδα)';

$labels['title_trusted_mtas'] = 'Έμπιστοι MTA';
$labels['label_trusted_mtas'] = 'Λίστα οριοθετημένη με κόμματα των authserv-id των mta που εμπιστεύεστε. Μόνο αποτελέσματα από αυτούς τους mta θα εμφανίζονται';

$labels['title_include_status'] = 'Εμφάνιση μόνο των παρακάτω καταστάσεων';
$labels['label_include_status1']  = 'Δε βρέθηκε υπογραφή';
$labels['label_include_status2']  = 'Βρέθηκε υπογραφή αλλά όχι κεφαλίδα αποτελεσμάτων πιστοποίησης';
$labels['label_include_status4']  = 'Όλες οι υπογραφές επικυρώθηκαν';
$labels['label_include_status8']  = 'Τουλάχιστο μια υπογραφή επικυρώθηκε';
$labels['label_include_status16']  = 'Υπογραφή τρίτης αρχής';
$labels['label_include_status32']  = 'Προσωρινή αποτυχία υπογραφής';
$labels['label_include_status64']  = 'Οριστική αποτυχία υπογραφής';

?>
