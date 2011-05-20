<?php
// Michael Thompson * 12/16/2005 * Created to automatically fill some context fields
include('../LDAPVars.php');
$LdapConn = ldap_connect($LdapServer);
if ($LdapConn) {
	$LdapBind = ldap_bind($LdapConn, $LdapUser, $LdapPassword);
	if ($LdapBind) {
		$LdapResults=ldap_search($LdapConn, $LdapBase, "cn=$ID", array("dn")); 
    	$LdapInfo = ldap_get_entries($LdapConn, $LdapResults);
     	if ($LdapInfo["count"] == 1) {
       		$LdapDN=$LdapInfo[0]["dn"];
       		$LdapParts = split(',',$LdapDN);
       		$LdapEntryCount = 0;
       		foreach ($LdapParts as $LdapPart) {
         		$LdapContext = "Context".$LdapEntryCount;
        		list(,$$LdapContext) = split('=',$LdapPart);
         		$$LdapContext = trim($$LdapContext);
         		$LdapEntryCount += 1;
      		}
     	}     
   	}
   ldap_close($LdapConn);
}
?> 