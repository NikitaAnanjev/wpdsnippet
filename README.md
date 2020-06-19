# wpdsnippet
WPD snippets plugin for Wordpress
INSTALLATION
************
GO TO WP-content/plugins
* Create "wpdsnippets" folder
*Clone git files inside of the new plugin "wpdsnippets" folder
* READY



****************************************************************
Plugin build as a test system for activate diactivate and store and execute usefull WordPress snippets.
1.
It grabs snippets from WPDistro.com api
API: https://wpdistro.com/wp-json/wp/v2/posts/

2.
Then it stores content into the wp-press posttypes  

                    'post_type' => 'wpd_snippets'
3.
Then it has AXAJ function to activate or diactivate a chosen snippet.
Activated snippet gets true or false value in the database as post_meta

                    'key' => 'snippet_active',
              
4. Then we get snippets back from database with indicated active value " if (snippet_active == 1)"

5. Logicaly we need to execute a function, and here I have difficulties. Normally you are able to do so with eval() PHP function
but it parse a string in some wrong format. I have tried to read php documentation upon the eval() funciton. It has some specifict 
of how you structure the string. 


P.S.

The problem can be fixed with proper sanitazer or with help of preg_replace() function.
Tried both but probably have luch of experience within these fields.

Hope you have some sucggestion.
