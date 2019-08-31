@echo off

if "%DIV_KIT_HOME%"=="" goto error

php %DIV_KIT_HOME%\bin\cli.php %*
goto end

:error
echo -  
echo ERROR: Enviroment variable DIV_KIT_HOME is not set in the system's properties.
echo -

:end
