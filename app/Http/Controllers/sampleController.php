use Illuminate\Support\Facades\Queue;
use App\Jobs\testQueue;

public function test_queue() {
	for($i=0; $i<=10; $i++){
        Queue::push(new testQueue(array('queue' => $i)));
		echo "successfully push";
    }
}