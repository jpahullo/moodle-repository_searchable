# Searchable Moodle repository

A filesystem-based repository focused on searchable capabilities, ideal for
directories with lots (thousands) of files.

## Installation

1. Clone this repository or unzip the archive into repository/searchable.
1. Go to Site Administration -> Notifications to install it.

## Set up

1. Go to Site Administration -> Plugins -> Repositories -> Searchable filesystem to:
 1. Set up this repository and user capabilities.
 1. Build instances of searchable repositories pointing to subdirectories under
 MOODLEDATA/repository/. They must not be links.

## About this repository

We build course backups from Moodle courses, with thousands of backup files.
When trying to mount the directory as a filesystem repository instance,
it hangs out, too many files to list on the web.

Since we simply need to pick up some file at a time, we thought on building
a repository instance, based on server-side filesystem, that does not list
files by default. Actually, it should show you a search form to then list only
matching files with the typed search criteria. It may be extensible to users if
administrators want to, too.

To do so, we based this repository on the current filesystem repository from
the Moodle core, including the ideas behind other searchable repositories,
make it work and then applying some Domain-driven design concepts to the
developed code, for a clearer, more sustainable code along time.

## Author

Jordi Pujol-Ahulló <jpahullo@gmail.com>

## License

GPL 3
