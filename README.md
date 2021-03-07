# etherpad-for-joomla
Joomla plugin to connect with etherpad

### Plugin usage


`
{etherpad x}
`
Replace x with the unique ID for the page you'd like to list documents for.

### Example usage

Imagine you have two seperate groups on your Joomla site and users from those groups need to collaborate on the etherpad documents but not have access to the other groups documents.

Create two articles with syntax {etherpad 1} and {etherpad 2} respectively.

Users acessing the first group's page will be provided with a list of all the documents in the first group and ability to create new documents for the first group.

In Joomla end we store the document/group/author id in SQL and is used to retrive and list documents when the above shortcode in rendered.

Read https://etherpad.org/doc/v1.3.0/#index_overview to understand how etherpad works with users/sessions/groups and documents.

