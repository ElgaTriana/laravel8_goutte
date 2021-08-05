<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;


class UpdateKlienCam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updatekliencam';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $var=\DB::table('cam_tabel_update_klien as a')->select('a.id_client_account_del', 'a.id_client_account_baru', 'b.id_client_account')
        ->leftJoin('cam_event_client as b', 'b.id_client_account', 'a.id_client_account_del')
        ->where('a.ket', "Y")
        ->where('a.ket_update', "update_langsung")
        ->get();

        $this->info('Memulai Konvert');

        foreach ($var as $key=>$value) {
            $ganti_id=\DB::table('cam_event_client as a')->where('a.id_client_account', $value->id_client_account_del)
            ->where('type', "PERSONAL")
            ->update(
                [
                    'id_client_account'=>$value->id_client_account_baru,
                    'id_client_account_backup'=>$value->id_client_account,
                    'update_user'=>"elga.triana@mncgroup.com",
                    'updated_at'=>Carbon::now()
                ]
            );

            $this->info('No '.$key.' Data id client account '.$value->id_client_account_del. ', telah di ubah menjadi '.$value->id_client_account_baru);
        }

        $this->info('Selesai');
    }
}
