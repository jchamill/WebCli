Web Based Command-Line Interface Framework
==============
Easily create a command-line based website. Write your own
command classes, similar to writing shell scripts.

Warning
--------------
This project is still very new and things are changing as the
ideas flow. The basics work, but I'm still adding features. Once
things move forward more I'll create an alpha or beta branch, but
this is all dev right now.

Installation
--------------
Run "composer install"

Extending
--------------
Write your own command classes and place them in the "commands"
directory. There is an "example" command that illustrates all
of the options, use this as a starting point.

Configuration
--------------
You can customize messages that are displayed on the site by
editing config/config.php.

Use Cases
--------------
To be honest, I'm not totally sure. I created this just
because I thought it'd be cool to have a command-line website.
Imagine users come to your site, you display a welcome message,
maybe tell them to type "help". They get a list of available
commands, that parts up to you. Make api requests to a separate
backend site that can display pages, blog posts, etc. Write
a twitter command that can pull tweets, post tweets, etc. This
is a playground, you can play too.

Road Map
---------------
-Persist data (complete)
-System commands (in progress)
-Remote Commands (in progress)
-Welcome message to be displayed as terminal output
-Read input from file
-Output to file/download
-Pipe commands