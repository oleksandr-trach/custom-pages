MODULE DESCRIPTION

This module is used to extend Amasty Custom Pages module.
The client wanted to have a possibility to create about 2000-3000 records of custom pages by importing a CSV file.
Because this is not so heavy operation it was decided to use web interface and not CLI command.

New "Import" button was added to the Custom Pages grid. After clicking, you're redirected to the import page
which is just a simple UI From component. For this component to work
entity collection is mandatory, that is why dummy empty collection was used. Since we don't use any entity or data on the form there is an empty data provider as well.
To import the FileUploader component is used on the form.

All records are validated and if there is an error while processing the record, it will be logged into log file, where you will be able to see what exactly caused the issue along with raw data of the record.
