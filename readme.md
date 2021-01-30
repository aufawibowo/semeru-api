# refactoring
# 1. Daftar Project
# 2. Hasil Refactoring

- [refactoring](#refactoring)
- [1. Daftar Project](#1-daftar-project)
- [2. Hasil Refactoring](#2-hasil-refactoring)
  - [2.1 Aktivitas Refactoring: Implementing SRP](#21-aktivitas-refactoring-implementing-srp)
  - [2.2 Aktivitas Refactoring: Implementing OCP](#22-aktivitas-refactoring-implementing-ocp)
  - [2.3 Aktivitas Refactoring: Implementing LSP](#23-aktivitas-refactoring-implementing-lsp)
  - [2.4 Aktivitas Refactoring: Implementing ISP](#24-aktivitas-refactoring-implementing-isp)
  - [2.5 Aktivitas Refactoring: Implementing DIP](#25-aktivitas-refactoring-implementing-dip)
  - [2.6 Aktivitas Refactoring: Naming Classes](#26-aktivitas-refactoring-naming-classes)
  - [2.6 Aktivitas Refactoring: Naming Method](#26-aktivitas-refactoring-naming-method)
  - [2.7 Aktivitas Refactoring: Naming Method](#27-aktivitas-refactoring-naming-method)
  - [2.8 Aktivitas Refactoring: Adding clean comments that convey intent](#28-aktivitas-refactoring-adding-clean-comments-that-convey-intent)
  - [2.9 Aktivitas Refactoring: Removing zombie code](#29-aktivitas-refactoring-removing-zombie-code)
  - [2.10 Aktivitas Refactoring: Fail fast](#210-aktivitas-refactoring-fail-fast)
  - [2.11 Aktivitas Refactoring: Validating Null](#211-aktivitas-refactoring-validating-null)
  - [2.12 Aktivitas Refactoring: Handling dates using date objects instead of strings](#212-aktivitas-refactoring-handling-dates-using-date-objects-instead-of-strings)

## 2.1 Aktivitas Refactoring: Implementing SRP
- **Kategori**: SOLID
- **Permasalahan**: Spaghetti Code didalam satu controller, dimana semua implementasi input/output handlers, implementasi bisnis, dan database query berada di dalam satu fungsi.
- **Solusi**: Untuk handlers, dipecah menjadi kelas request dan service sendiri\

Potongan code sebelum refactoring
```php
/*
https://github.com/aufawibowo/semeru-api/blob/2.9/app/Http/Controllers/RtpoControllerNew.php 1162
*/
public function approve_reschedule_sik(Request $request)
{
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$periode = date('Y-m');

	$sik_no = $request->input('sik_no');
	$username = $request->input('username');
	$reason = $request->input('reason');
	$is_approved = $request->input('is_approved');
	//$username = 'enggarrio';
	settype($is_approved, "boolean");
	$rtpo_users_data = DB::table('users')
	->select('*')
	->where('username',$username)
	->first();

	$rtpo_nik = $rtpo_users_data->id;
	$rtpo_cn = $rtpo_users_data->name;

	if($is_approved){
	$approve = DB::table('propose_reschedule')
	->where('sik_no',$sik_no)
	->update([
		'status' => 1,
		'status_desc' => 'WAITING FOR NOS APPROVAL',
		'rtpo_nik' => $rtpo_nik,
		'rtpo_cn' => $rtpo_cn,
		'last_updated' => $date_now,
		'is_sync' => 0,
	]);

	$res['success'] = 'OK';
	$res['message'] = 'Success';
	
	return response($res); 
	}
	else{
	$approve = DB::table('propose_reschedule')
	->where('sik_no',$sik_no)
	->update([
		'status' => 2,
		'status_desc' => 'REJECTED BY RTPO',
		'reject_reason' => $reason,
		'rtpo_nik' => $rtpo_nik,
		'rtpo_cn' => $rtpo_cn,
		'last_updated' => $date_now,
		'is_sync' => 0,
		]);

	$res['success'] = 'OK';
	$res['message'] = 'Success';
	
	return response($res); 
	}
}
```

Potongan code setelah refactoring
```php
// Request.php
<?php


namespace Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK;


class Request
{
    public ?string $sik_no;
    public ?string $username;
    public ?string $reason;
    public ?string $is_approved;

    /**
     * Request constructor.
     * @param string|null $sik_no
     * @param string|null $username
     * @param string|null $reason
     * @param string|null $is_approved
     */
    public function __construct(?string $sik_no, ?string $username, ?string $reason, ?string $is_approved)
    {
        $this->sik_no = $sik_no;
        $this->username = $username;
        $this->reason = $reason;
        $this->is_approved = $is_approved;
    }

    public function validate()
    {
        $errors = [];

        if (!isset($this->sik_no)) {
            $errors[] = 'sik no must be specified';
        }

        if (!isset($this->username)) {
            $errors[] = 'username must be specified';
        }

        if (!isset($this->reason)) {
            $errors[] = 'reason must be specified';
        }

        if (!isset($this->is_approved)) {
            $errors[] = 'is approved must be specified';
        }

        return $errors;
    }
}
```
```php
// Service.php
<?php


namespace Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK;


use Semeru\Rtpo\Core\Domain\Exceptions\ValidationException;
use Semeru\Rtpo\Core\Domain\Models\Rtpo;
use Semeru\Rtpo\Core\Domain\Models\SikNo;
use Semeru\Rtpo\Core\Domain\Repositories\RtpoRepository;
use Semeru\Rtpo\Core\Domain\Repositories\SikRepository;

class Service
{
    private SikRepository $sikRepository;
    private RtpoRepository $rtpoRepository;

    /**
     * Service constructor.
     * @param SikRepository $sikRepository
     * @param RtpoRepository $rtpoRepository
     */
    public function __construct(SikRepository $sikRepository, RtpoRepository $rtpoRepository)
    {
        $this->sikRepository = $sikRepository;
        $this->rtpoRepository = $rtpoRepository;
    }

    public function execute(Request $request)
    {
        $errors = $request->validate();

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $rtpo = new Rtpo(
            $this->rtpoRepository->getRtpoId(),
            $this->rtpoRepository->getRtpoUserData(),
            new SikNo($request->sik_no),
            new \DateTime('now')
        );

        $waitingForApprovalAccepted = $this->sikRepository->setWaitingForApproval($rtpo);

        if($waitingForApprovalAccepted)
        {
            return 'OK';
        }
        else{
            $this->sikRepository->rejectByRtpo($rtpo);
            return 'Rejected';
        }
    }
}
```
Sehingga implementasi pada controller menjadi seperti berikut:
```php
/*
https://github.com/aufawibowo/semeru-api/blob/refactored/app/modules/rtpo/Presentation/Controllers/Controller.php
*/
public function requestMBPToSiteDownAction()
{
    $sik_no = $this->request->get('sik_no');
    $username = $this->request->get('username');
    $reason = $this->request->get('reason');
    $is_approved = $this->request->get('is_approved');

    $request = new ApproveRescheduleSIKRequest(
        $sik_no,
        $username,
        $reason,
        $is_approved
    );

    $service = new ApproveRescheduleSIKService(
        $this->di->get('sikRepository'),
        $this->di->get('rtpoRepository')
    );

    try {
        $result = $service->execute($request);

        $this->sendData($result);
    } catch (\Exception $e) {
        $this->handleException($e);
    }
}
```

## 2.2 Aktivitas Refactoring: Implementing OCP
- **Kategori**: SOLID
- **Permasalahan**: Penempatan perintah SQL yang digunakan untuk mengambil data pada satu tempat yang sama dengan handler request, response service, dan implementasi bisnis.
- **Solusi**: Membuat object repository yang digunakan oleh service. Sehingga, jika terdapat kebutuhan tambahan yang akan datang dapat melakukan ekstensi tanpa merusak existing query yang sudah ada.

Potongan code sebelum refactoring
```php
/*
https://github.com/aufawibowo/semeru-api/blob/2.9/app/Http/Controllers/RtpoControllerNew.php 1162
*/
public function approve_reschedule_sik(Request $request)
{
    //...

	if($is_approved){
	$approve = DB::table('propose_reschedule')
	->where('sik_no',$sik_no)
	->update([
		'status' => 1,
		'status_desc' => 'WAITING FOR NOS APPROVAL',
		'rtpo_nik' => $rtpo_nik,
		'rtpo_cn' => $rtpo_cn,
		'last_updated' => $date_now,
		'is_sync' => 0,
	]);

	$res['success'] = 'OK';
	$res['message'] = 'Success';
	
	return response($res); 
	}
	else{
	$approve = DB::table('propose_reschedule')
	->where('sik_no',$sik_no)
	->update([
		'status' => 2,
		'status_desc' => 'REJECTED BY RTPO',
		'reject_reason' => $reason,
		'rtpo_nik' => $rtpo_nik,
		'rtpo_cn' => $rtpo_cn,
		'last_updated' => $date_now,
		'is_sync' => 0,
		]);

	$res['success'] = 'OK';
	$res['message'] = 'Success';
	
	return response($res); 
	}
}
```

Potongan code setelah refactoring
```php

<?php


namespace Semeru\Rtpo\Core\Domain\Repositories;


use Semeru\Rtpo\Core\Domain\Models\Rtpo;

interface SikRepository
{
    public function setWaitingForApproval(Rtpo $rtpo);
    public function rejectByRtpo(Rtpo $rtpo);
}
```

```php

<?php


namespace Semeru\Rtpo\Core\Domain\Repositories;


interface RtpoRepository
{
    public function getRtpoUserData(string $username);
    public function getRtpoId();
}
```

## 2.3 Aktivitas Refactoring: Implementing LSP
- **Kategori**: SOLID
- **Permasalahan**: Spaghetti Code didalam satu controller, dimana semua implementasi input/output handlers, implementasi bisnis, dan database query berada di dalam satu fungsi.
- **Solusi**: Untuk handlers, dipecah menjadi kelas request dan service sendiri\

Potongan code sebelum refactoring

## 2.4 Aktivitas Refactoring: Implementing ISP
- **Kategori**: SOLID
- **Permasalahan**: Spaghetti Code didalam satu controller, dimana semua implementasi input/output handlers, implementasi bisnis, dan database query berada di dalam satu fungsi.
- **Solusi**: Untuk handlers, dipecah menjadi kelas request dan service sendiri\

Potongan code sebelum refactoring

## 2.5 Aktivitas Refactoring: Implementing DIP
- **Kategori**: SOLID
- **Permasalahan**: Spaghetti Code didalam satu controller, dimana semua implementasi input/output handlers, implementasi bisnis, dan database query berada di dalam satu fungsi.
- **Solusi**: Untuk handlers, dipecah menjadi kelas request dan service sendiri\

Potongan code sebelum refactoring

## 2.6 Aktivitas Refactoring: Naming Classes
- **Kategori**: Clean Code
- **Permasalahan**: Spaghetti Code didalam satu controller, dimana semua implementasi input/output handlers, implementasi bisnis, dan database query berada di dalam satu fungsi.
- **Solusi**: Pemecahan berdasarkan metode domain driven design. Dimana object paling primitif adalah domain model.

Potongan code sebelum refactoring
- *menggunakan cuplikan kode yang sama dengan aktivitas refactoring 2.1.*  

Potongan code setelah refactoring
```php
<?php


namespace Semeru\Rtpo\Core\Domain\Models;


class Rtpo
{

}

class Address
{

}

class Coordinate
{

}

class Date
{

}

```
## 2.6 Aktivitas Refactoring: Naming Method
- **Kategori**: Clean Code
- **Permasalahan**: Penamaan method tidak konsisten. Terdapat method yang menggunakan konvensi camelCase atau snake_case didalam satu kelas yang sama.
- **Solusi**: Mengikuti best practice penamaan method PHP.

Potongan code sebelum refactoring
```php
public function approve_reschedule_sik();
public function cekSamplingFormula();
```

Potongan code setelah refactoring
```php
public function setWaitingForApproval();
public function rejectByRtpo();
```

## 2.7 Aktivitas Refactoring: Naming Method
- **Kategori**: Clean Code
- **Permasalahan**: Penamaan method tidak ambigu.
- **Solusi**: Mengubah menjadi lebih singkat, padat, dan jelas.

Potongan code sebelum refactoring
```php
public function tanggal_bulan_tahun_indo_tiga_char();
```

Potongan code setelah refactoring
```php
public function getMonthInThreeCharacters();
```

## 2.8 Aktivitas Refactoring: Adding clean comments that convey intent
- **Kategori**: Clean Code
- **Permasalahan**: Terkadang pemberian nama metode sedikit menimbulkan multitafsir
- **Solusi**: Menambah komentar yang menjelaskan fungsi tersebut.

Potongan code sebelum refactoring
```php
public function getMonthInThreeCharacters();
```

Potongan code setelah refactoring
```php
/**
* Function getMonth() return month in three character: Jan, Feb, Mar, etc.
* @return string
*/
public function getMonthInThreeCharacters(): string
{
    return $this->getMonth();
}
```

## 2.9 Aktivitas Refactoring: Removing zombie code
- **Kategori**: Clean Code
- **Permasalahan**: Zombie code merupakan cuplikan kode yang ditinggal dengan komentar sehingga tidak bisa dijalankan.
- **Solusi**: Hapus kode.

Potongan code sebelum refactoring
```php
// public function reject_reschedule_sik(Request $request)
// {
//   date_default_timezone_set("Asia/Jakarta");
//   $date_now = date('Y-m-d H:i:s');
//   $periode = date('Y-m');

//   $sik_no = $request->input('sik_no');
//   $reason = $request->input('reason');
//   $username = $request->input('username');

//   //$username = 'enggarrio';

//   $rtpo_users_data = DB::table('users')
//   ->select('*')
//   ->where('username',$username)
//   ->first();

//   $rtpo_nik = $rtpo_users_data->id;
//   $rtpo_cn = $rtpo_users_data->name;

//   $approve = DB::table('propose_reschedule')
//   ->where('sik_no',$sik_no)
//   ->update([
//     'status' => 2,
//     'status_desc' => 'REJECTED BY RTPO',
//     'reject_reason' => $reason,
//     'rtpo_nik' => $rtpo_nik,
//     'rtpo_cn' => $rtpo_cn,
//     'last_updated' => $date_now,
//     'is_sync' => 0,
//   ]);

//   $res['success'] = true;
//   $res['message'] = 'SUCCESS';
	
//   return response($res); 
// }
```

Potongan code setelah refactoring
- *kode dihapus*

## 2.10 Aktivitas Refactoring: Fail fast
- **Kategori**: Defensive Coding
- **Permasalahan**: Semakin modular sebuah project (i.e. Clean Arch, Microservice), semakin kompleks cara untuk mentracing sebuah bug yang tercipta.
- **Solusi**: Mereturn eksepsi sedini mungkin.
  
Cuplikan code
```php
// app/modules/rtpo/Core/Application/Services/ApproveRescheduleSIK/ApproveRescheduleSIKService.php
public function execute(ApproveRescheduleSIKRequest $request)
    {
        $errors = $request->validate();

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
```

## 2.11 Aktivitas Refactoring: Validating Null
- **Kategori**: Defensive Coding
- **Permasalahan**: Terkadang input bisa bernilai null. Proses validasi diperlukan.
- **Solusi**: Membuat fungsi validasi null.

Cuplikan code
```php
<?php


namespace Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK;


class ApproveRescheduleSIKRequest
{
    public ?string $sik_no;
    public ?string $username;
    public ?string $reason;
    public ?string $is_approved;

    /**
     * ApproveRescheduleSIKRequest constructor.
     * @param string|null $sik_no
     * @param string|null $username
     * @param string|null $reason
     * @param string|null $is_approved
     */
    public function __construct(?string $sik_no, ?string $username, ?string $reason, ?string $is_approved)
    {
        $this->sik_no = $sik_no;
        $this->username = $username;
        $this->reason = $reason;
        $this->is_approved = $is_approved;
    }

    public function validate()
    {
        $errors = [];

        if (!isset($this->sik_no)) {
            $errors[] = 'sik no must be specified';
        }

        if (!isset($this->username)) {
            $errors[] = 'username must be specified';
        }

        if (!isset($this->reason)) {
            $errors[] = 'reason must be specified';
        }

        if (!isset($this->is_approved)) {
            $errors[] = 'is approved must be specified';
        }

        return $errors;
    }
}
```

## 2.12 Aktivitas Refactoring: Handling dates using date objects instead of strings
- **Kategori**: Defensive Coding
- **Permasalahan**: Input string dapat beraneka ragam. 
- **Solusi**: Object date membantu untuk mengimplementasi pembuatan object date dengan bersifat strong type.

Cuplikan code
```php
public function execute(ApproveRescheduleSIKRequest $request)
    {
        $errors = $request->validate();

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $rtpo = new Rtpo(
            $this->rtpoRepository->getRtpoId(),
            $this->rtpoRepository->getRtpoUserData(),
            new SikNo($request->sik_no),
            new \DateTime('now')
        );

    }
```