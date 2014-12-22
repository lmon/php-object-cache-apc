php-object-cache-apc
====================

testing out some apc caching

with the goal of reducing PHP and Datbase calls, i have wrapped a set of existing function calls in an object and hooked that data to APC.
the data is checked for existence in the APC and then retreived when it doesnt exist.

ultimately the whole class instance could be saved and retreived in the same way, if no data in the child objects had changed.

