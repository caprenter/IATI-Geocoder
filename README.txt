=International Aid Transparency Initiative (IATI) Geocoder Information=
 
International Aid Transparency Initiative (IATI) Geocoder is an application to generate IATI compliant
location XML from a specific set of data supplied by the World bank.
This may be useful for other datasets and other transformations.

See http://iatistandard.org/ for more info.

==Licence==
GNU General Public License (except where stated)
see the Copying directory

==Install==
This is built as php_cli file, but you could run it on your server

===Get some data===
To run a transformation you will need a copy of the data from:
http://open.aiddata.org/content/index/geocoding
I've used this:
http://open.aiddata.org/WeceemFiles/_ROOT/File/WB-GeocodedDataset.zip

Included is a country list of ISO 3166 codes taken from:
http://iatistandard.org/codelists/country

==Run==
By passing the varable:
headers=TRUE
the first line of the csv headers will be ignored


