## URL Builder Template

This WordPress plugin allows you to upload a CSV file of Cities and States, creates a new rewrite rule to build dynamic URLs, and allows for the template to be editable via the plugin interface. Finally, a full list of links of all cities and states should appear at the bottom of the page. The goal is to build pages with city and state names to appear in a template.

For example, let's say you had the following list you were going to import.

Portland, Oregon
Denver, Colorado
New York, New York
Gainesville, Florida

If you assigned the page 'Example' the _UBT Template_, and the slug of that page was 'example', the rewrite rules would allow you to display:
https://yoursitename.com/example/Oregon/Portland/ 
and you'd be able to see your customized template. We'll use that URL for the duration of the example.

But it's not populated to begin with, so you include: **$city**, **$state** and **$city_state** anywhere in your template. These variables will display the City and State name displayed in the URL.

For example, if your template looks like this:
``` <h3>My Great City of $city</h3>```

And your URL was as above, it would appear like this:
###My Great City of Portland

If the URL was: https://yoursitename.com/example/Florida/Gainesville/, it would appear like this:
###My Great City of Gainesville 


###Plugin Goals:
* Allow for upload of 2-column CSV file to custom database table
* Builds custom page template 
* Rewrite URL rule to allow for State/City URL structure of above uploaded database
* Builds page template to allow for insertion of City name and State name as well as custom HTML
* Display list of uploaded CSV city/state list as these custom dynamic URLs

###Instructions for Use
* Upload zipped file to add new plugin to WP instance
* Upload CSV file of desired Cities and States
* Assign page template _UBT Template_ to the page you'd like to use for this
* Update template on Batch Upload plugin settings page

####Notes:
* Assign only one page the _UBT Template_ template. If you assign multiple, only the first page assigned the template will reflect the custom template content
* No capitalization is supported - whatever is 

####TODO:
* Update regex in rewrite rule to allow for either homepage or sub page to display custom template
* Allow storage of multiple templates, depending on the Page slug name

Project was for a client and had specific focus, and only a limited amount of development was done on this.
