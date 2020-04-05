ContactUs
=========

MediaWiki Extension: Contact the relevant staff members of a wiki through one page.

People are placed into "groups" for different types of emails. When a user utilizes Special:Contact_Us, they will be able to choose a "type" of email, such as tech support, administration, etc.

Settings
========
$wgContactUs_Recipients
Associative array of recipients and what types of emails they should receive. If this is not set, the extension will not function.
ex:
$wgContactUs_Recipients['Justin'] = array('tech', 'administration', 'affiliation');

If $wgContactUs_DisableGroups is set to true, you can just set the people to true.
ex:
$wgContactUs_Recipients['Justin'] = true;

$wgContactUs_Groups
What types of emails people can send and what the option should be called on the form. If this is not set, $wgContactUs_DisableGroups MUST be true.
ex:
$wgContactUs_Groups['tech'] = 'Bug reports';

$wgContactUs_DisableGroups
Whether or not to disable the groups and have all users receive emails. Evaluates to false by default.
ex:
$wgContactUs_DisableGroups = true;
