Pour exécuter les tests sur la base de données sqlite :

$ app/console doctrine:database:create --env=test
$ app/console doctrine:schema:update --env=test --force
$ phpunit -c app/
