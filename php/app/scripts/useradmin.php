#! /usr/bin/php
<?php
/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
require_once 'inc/headers.php';

function help()
{
	echo <<<HEREDOC
*********************************************
* Question data bank administration utility *
*********************************************
Allows the *listing*, the *creation*, the *update*, or the *deletion* of administration accounts.

List		useradmin --list		OR	useradmin -l
Create		useradmin --add login		OR	useradmin -a login
Delete		useradmin --delete login	OR	useradmin -d login
Password	useradmin --password login	OR	useradmin -p login

HEREDOC;
	exit(0);
}

function read_line_hidden()
{
	system('stty -echo');
	$line = '';

	while (($c = fgetc(STDIN)) != "\n")
	{
		$line .= $c;
	}
	
	system('stty echo');
	return $line;
}

function _list()
{
	$userMapper = new DB_Mapper_User;
	$list = $userMapper->findAll();
	
	if (($l = count($list)) == 0)
	{
		echo "No account.\n";
		exit(0);
	}
	
	for ($i = 0; $i < $l; $i++)
	{
		echo $list[$i]['user_name'], "\n";
	}
	
}

function add($login, $password)
{
	$userMapper = new DB_Mapper_User;
	$user = new DB_Model_User;
	$user->set_user_name($login);
	$user->set_password($password);
	
	try
	{
		$id = $userMapper->save($user);
	}
	
	catch (Exception $e)
	{
		echo "An error occured.\n", $e;
		exit(1);
	}
	
	if ( ! $id)
	{
		echo "An error occured.\n";
		exit(1);
	}
	
	echo "Login \"$login\" with password \"$password\" successfuly created.\n";
	exit(0);
}

function delete($login)
{
	$userMapper = new DB_Mapper_User;
	$l = $userMapper->deleteByLogin($login);
	
	if ($l > 0)
	{
		echo "Account \"$login\" deleted.\n";
		exit(0);		
	}
	
	else
	{
		echo "No account was deleted.\n";
		exit(1);
	}

}

function update($login, $password)
{
	$userMapper = new DB_Mapper_User;
	$user = $userMapper->findByLogin($login);
	
	if ( ! $user)
	{
		echo "No user for login \"$login\"\n";
		exit(1);
	}
	
	else
	{
		$user->set_password($password);
		
		try
		{
	    	$id = $userMapper->save($user);
		}
		
		catch (Exception $e)
		{
			echo "An error occured.\n$e\n";
			exit(1);
		}
		
	    
		if ($id)
		{
			echo "Password changed to \"$password\" for login \"$login\".\n";
			exit(1);
		}
		
		else
		{
			echo "An error occured.\n";
			exit(0);
		}
	}
	
}

try
{
	$opts = new Zend_Console_Getopt(
		array(
			'help|h' => 'Show an help message and exits.',
			'list|l' => 'List all registered accounts',
			'add|a=s' => 'Add an account.',
			'delete|d=s' => 'Delete an account.',
			'password|p=s' => 'Change an account password.',
		)
	);
}

catch (Exception $e)
{
	echo $e->getUsageMessage();
	exit(1);
}

try
{

	if ( ! $opts->toArray())
	{
		echo $opts->getUsageMessage();
		exit(0);
	}

}

catch (Exception $e)
{
	echo $opts->getUsageMessage();
	exit(1);
}

if ($opts->getOption('help'))
{
	help();
}


if ($list = $opts->getOption('list'))
{
	_list();
}

if ($login = $opts->getOption('add'))
{
	$userMapper = new DB_Mapper_User;
	
	if ($userMapper->loginExists($login))
	{
		echo "An account named \"$login\" already exists.\n";
		exit(1);
	}

	echo "Enter password for user $login :\n";
	$password = read_line_hidden();
	echo "Retype password for user $login :\n";
	$_password = read_line_hidden();
	
	if ($password != $_password)
	{
		echo "Passwords didn\'t match\n";
		exit(1);
	}

	add($login, $password);
}

if ($login = $opts->getOption('delete'))
{
	delete($login);
}

if ($login = $opts->getOption('update'))
{
	
	echo "Enter password for user $login :\n";
	$password = read_line_hidden();
	echo "Retype password for user $login :\n";
	$_password = read_line_hidden();
	
	if ($password != $_password)
	{
		echo "Passwords didn\'t match\n";
		exit(1);
	}

	update($login, $password);
}