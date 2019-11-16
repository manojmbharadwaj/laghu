**Project Intro**

Laghu(Small in Kannada) Migration Tool!

---

## Intro

Laghu is small migration tool. The flow is simple. First we will create a migration file. Add SQL to the created file. Run the migration!.
When we run the migration, the tool check weather the file is all-ready migrated or not. If it is being migrated it won't run the SQL.  
The tool will create Migration directory, migration table(laghu_migrations) when you run the script. DO NOT CHANGE DIRECTORY NAME and TABLE NAME unlsess you change the laghu.php script as well.  
In small projects I found it is easier to have a migration based on raw SQL scripts than using a framework for it.

## Config
Update config.php with database credentials

## Commands

1. Create Migration:  
    php laghu.php create [file_suffix]  
    php laghu.php create ct-user_detail -> this will create the file [timestamp]ct-user_detail in migration directory.  
    Naming conventions (optional. Suggested for readiblity):  
        ct -> create table  
        mt -> modify table  
        dt -> SQL contains Data  
2. Run migration  
    php laghu.php migrate  
    The command will run the migration outputs no. of files migrated successfully.  