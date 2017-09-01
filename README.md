## Advanced Config
### What does it do?
Right now not much. It gives you a different avenue to define config settings
that is similar to how it is done in settings.php and they can be appended to
existing admin setting pages defined in settings.php.

But those config settings will suddenly become able to be overridden at a category level!
Categories inherit configuration settings from their parent categories if they don't
anything set for themselves. This works all the way up to the site configuration level.

This feature came about because at Monash Uni we needed to be able to set some pluging
settings depending on which faculty(category) a course was in. At first there was one
setting but we soon discovered there were many so instead we set about to build a
generic solution to a generic problem.

See an example of it with pictures on [my blog](https://blog.segfault.id.au/2017/09/01/new-plugin-advanced-config/)

### Future work
Long term the aim of the project is to disconnect the defining, validating and writing
of configuration settings from the Moodle settings tree web interface.
This would allow for settings to be read and written from web services and CLI etc.
* Ability to view the tree of configuration inheritance for an individual setting
* Auditable setting edit log(critical)
* Web service for viewing/editing configuration data
* CLI for viewing/editing configuration data
* Configuration flag for settings to enable inheritance(default to site level config)
* World domination! (integration with Moodle core, maybe, someday, if they like it)

### How do I use it?
Look at the classes/model/settings.php and classes/model/tree.php files.
They are interfaces that you must implement to define configuration settings.

Finally to retrieve a context specific config use the context_config::get_config
function. It is similar to how the existing get_config functionality works.