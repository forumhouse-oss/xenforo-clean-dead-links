What is this?
======================

This is a skeleton of the addon, that can be used to clean your XenForo forum of dead links.

It takes a list of dead links on your forum and cleans them out from the database, carefully editing forum contents.

I say, that it is a skeleton, because it is not in the end-user quality right now. Rather, it is a skeleton for
a XenForo developer to create your own version of content-cleaning addon. However, this code without changes is
running on our https://www.forumhouse.ru forum, which is rather big. So, the code is working.

This skeleton is unlikely to receive any stable version, since this is a part of our forever development branch. But
anyway pull requests are welcome.

Requirements
======================

XenForo 1.0 was out at the times, when Composer was not embraced by everyone in the PHP community. Much has changed 
since those times. I cannot imagine to do anything in PHP without the use of Composer. So, this addon requires you
to connect Composer to your XenForo installation.

It could be done very easily. You can put `composer.json` to the root of your XenForo installation, run 
`composer update`, wait for composer to download project dependencies and add 
`require_once(__DIR__ . '/../vendor/autoload.php');` to the end of your `library/config.php` file. 

Now you can use all features of libraries, supported by Composer in your XenForo project.

Installation
======================

You can just clone repository into your XenForo installation using the following Git command:
```
git clone https://github.com/fhteam/xenforo-clean-dead-links.git library/FH/LinkCleaner
```

Architecture
======================

The original task of this addon was to take pairs of (content-url, dead-link) and to clean those dead links out
of forum.

Extractors
----------------------

So, the first line of processing is an extractor. It takes some file as an input, and produces many 
`FH_LinkCleaner_Engine_Extractor_FileEntry` after parsing it.

Sorters
----------------------

Next come sorters. Sorters take (content-url, dead-link) pairs and produce 
`FH_LinkCleaner_Engine_Sorter_CleanCollection` items. Each collection contains 
`FH_LinkCleaner_Engine_Sorter_CleanItem` items that belong to the same type ('thread' for example).

`FH_LinkCleaner_Engine_Sorter_CleanCollection` contains link type, content processor class (see below) and items to 
clean respectively.

`FH_LinkCleaner_Engine_Sorter_CleanItem` contains content id to be cleaned and a list of dead links to be removed
from the content.

Content processors
----------------------

Content processors know how to read/write various content types (such as threads, user diaries etc). They take a 
list of `FH_LinkCleaner_Engine_Sorter_CleanItem`, read associated content from database and clean content using
some cleaners (see below).

Cleaners
----------------------

A cleaner knows how to clean a particular chunk of the text disregarding its source. It just takes a string and
produces cleaned content. All cleaners descend from `FH_LinkCleaner_Engine_Cleaner_Abstract`. A cleaner may yield null
if it detects, that no cleaning was required.

Logging
======================

An extensive logging is a must-have part of any project you develop. So, this addon passes an instance of 
`Monolog\Logger` around for use in almost any part of the addon.

Each content change is diff`ed and diff result is also logged for future reference.

Epilogue
======================

Let me repeat once more. This is not an end-user-ready addon. This is only a piece of code you may take and
re-implement for your specific task.