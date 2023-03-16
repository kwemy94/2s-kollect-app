<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class viderBDCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vider:db {--donnees : Supprimmer seulement les données }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vider la base de données de toutes ses tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        # Vérifier si on est en production ou en local
        $environnement = config('app.env'); #path du fichier .env
        if ($environnement != 'local') {
            exit("Cette commande ne peut être exécuté qu'en local ".PHP_EOL);
        }

        #Vérifier si l'option données a été utilisé
        $donneesSeulement = $this->option('donnees') == null ? false : true ;

        if ($donneesSeulement) {
            # Confirmation de la suppression effective des données
            if (!$this->confirm('Désirez-vous vraiment vider les tables de la db ?')) {
                exit('Commande annulée '.PHP_EOL);
            } 
        }
        else {
            if (!$this->confirm('Désirez-vous vraiment supprimer toutes les tables de la db ?')) {
                exit('Commande annulée '.PHP_EOL);
            }
        }

        # Ignorer les contraintes d'intégrités refférentielle
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        # Retrouver la liste des tables
        $tables = DB::select('SHOW TABLES');

        if (!$donneesSeulement) {
            # Détruire chacune des tables
            foreach ($tables as  $tableStdClass) {
                foreach ($tableStdClass as $key => $table) {
                    DB::statement("DROP TABLE $table");
                }
            }
        }
        else {
            #Vider uniquement les tables
            foreach ($tables as  $tableStdClass) {
                foreach ($tableStdClass as $key => $table) {
                    DB::table($table)->delete();
                }
            }
        }

        # Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        # Message de confirmation 
        if ($donneesSeulement) {
            $this->info("Toutes les données de la base de données ont été supprimé");
        }
        else {
            $this->info("Toutes les tables de la base de données ont été supprimé !");
        }
        
        return 0;
    }
}
