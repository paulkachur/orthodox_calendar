# orthodox_calendar
calculates core data about the liturgical year

This repository contains the source code for two utilities:

http://grandtier.com/oca/calendar.php -- an Orthodox calendar
http://grandtier.com/oca/zachalos.php -- lookup of scripture reading by pericope number

However, the purpose of this repository is to present lib/core.lib.php, the engine that powers the utilities and contains the algorithm for determining where we are in a liturgical year.

The heart of this system is the concept of a 'paschal year,' which begins with Zacchaeus Sunday and continues to the next one. Within the paschal year, every day has an integer value based on its relation to Pascha, which we call the "pday." The pday of Pascha is 0, the pday of Zacchaeus Sunday is -77, the pday of Palm Sunday is -7, the pday of Ascension is 39, the pday of Pentecost is 49, etc.

Further information about pdays and calendar days is retrieved from the mysql 'days' table, and scripture readings associated with pdays and calendar days are defined in the 'readings' table. The data in these tables is according to the practice of the Orthodox Church in America. Those who wish to follow another tradition can edit these tables appropriately.

The 'readings' table does not define verses, but rather the book and pericope number. The 'zachalos' table defines the content of the pericopes.

Our versions of these three tables are provided in the 'sql' folder.

One may note that scripture readings are rendered in liturgical form, that is, instead of beginning at the beginning of the first verse and proceeding to the end of the last, they follow all the instructions given in the liturgical Gospel and Apostol (these instructions are encapsulated in the 'zachalos' table). But to make this work, our scripture database is seeded with special characters: an asterisk in the first verse of a block indicates that preceding text is ignored, and a vertical bar in the last verse of a block indicates that following text is dropped (often replaced by fields from the 'zachalos' table). Unfortunately, we cannot provide our 'scriptures' table as it contains copyrighted material.

This 'core' system does not include full listings of saints or services or troparia. It is intended to provide core information that can be used to find other information from extended databases. For example, at http://nycathedral.org, the cathedral's code base is loaded as a child of the core, and after taking the computations from core it draws on its own resources for saints, services, troparia, etc.

The calendar.php utility demonstrates all the information computed by the core. Clicking on any reading takes you to a page that displays the full reading, and underneath the reading is a dump of all variables produced by the functions used. In lib/core.REFERENCE.txt one may find information about these variables, as well as the fields in the various tables.
