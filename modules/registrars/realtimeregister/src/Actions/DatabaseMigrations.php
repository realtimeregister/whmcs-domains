<?php

namespace RealtimeRegister\Actions;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use RealtimeRegister\Request;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\InputStream;

class DatabaseMigrations extends Action
{
    public function __invoke(Request $request): void
    {
//echo '<pre>';
//        dump(Capsule::connection());
//        echo '<hr><hr>';
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('log_errors', 1);
        // https://gist.github.com/jaceju/cc53d2fbab6e828f69b2a3b7e267d1ed
        $capsule = new Capsule();

        $capsule->addConnection(Capsule::connection()->getConfig());
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $container = Container::getInstance();
        $databaseMigrationRepository = new DatabaseMigrationRepository($capsule->getDatabaseManager(), 'migration');
        if (!$databaseMigrationRepository->repositoryExists()) {
            $databaseMigrationRepository->createRepository();
        }
        $container->instance(MigrationRepositoryInterface::class, $databaseMigrationRepository);
        $container->instance(ConnectionResolverInterface::class, $capsule->getDatabaseManager());

//        /** @var Migrator $migrator */
//        $migrator = $container->make(Migrator::class);

        $migrator = new Migrator($databaseMigrationRepository, $capsule->getDatabaseManager(), new Filesystem());
//        $migrator->setConnection('default');
//        dd($migrator);
//        $migrator->setOutput(new SymfonyStyle(new ArrayInput([]), new OutputFormatter(false)));
//dd($migrator);
        $path = __DIR__ . '/../../migrations';
        $path = '/var/www/html/whmcs/modules/registrars/realtimeregister/migrations';
//dd($capsule->getConnection());
        $migrator->path($path);
        try {
//            die('here');

//            dd($migrator->getRepository());
            $ran = [];
//            dd(Collection::make(['/var/www/html/whmcs/modules/registrars/realtimeregister/src/Actions/../../migrations/2024-08-01_000001_create-contact-mapping.php'])
//                ->reject(function ($file) use ($ran) {
//                    return in_array($this->getMigrationName($file), $ran);
//                })->values()->all());
//            echo __METHOD__ . ':' . __LINE__;
//            die('here');
//            dd($migrator->getPendingMigrations(['/var/www/html/whmcs/modules/registrars/realtimeregister/migrations/2024-08-01_000001_create-contact-mapping.php'], []));
//die('here');
            logActivity('migrator has run??');
            $migrator->runPending(['/var/www/html/whmcs/modules/registrars/realtimeregister/migrations/2024-08-01_000001_create-contact-mapping.php'], []);
            logActivity('migrator has run!!');
        } catch (\Exception $e) {
            throw new \Exception($e);
            // Handle any other exceptions
            dd("An error occurred: " . $e);
        }

//        dd('we are here');
    }

    public function getMigrationName($path)
    {
        return str_replace('.php', '', basename($path));
    }
}
