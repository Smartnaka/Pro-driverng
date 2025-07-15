<?php
include 'include/db.php';  // assumes $conn is your MySQLi connection

$drivers = [
    [
        "first_name" => "Michael",
        "last_name" => "Adeyemi",
        "email" => "michael.adeyemi@gmail.com",
        "phone" => "+2348023456789",
        "password" => password_hash("Mich@2025!ad", PASSWORD_DEFAULT),
        "exp_years" => 9,
        "education" => "OND",
        "photo_path" => "https://randomuser.me/api/portraits/men/31.jpg",
        "nin" => "NIN89236001674",
        "acc_num" => "6209474018",
        "acc_name" => "Michael Adeyemi",
        "bank_name" => "First Bank",
        "skills" => "Attention to Detail, Punctuality",
        "speak" => "English, Yoruba",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Fatima",
        "last_name" => "Umar",
        "email" => "fatima.umar22@yahoo.com",
        "phone" => "+2347034567890",
        "password" => password_hash("Fat!Umar@25", PASSWORD_DEFAULT),
        "exp_years" => 7,
        "education" => "SSCE",
        "photo_path" => "https://randomuser.me/api/portraits/women/45.jpg",
        "nin" => "NIN30322829883",
        "acc_num" => "3629934984",
        "acc_name" => "Fatima Umar",
        "bank_name" => "Zenith Bank",
        "skills" => "Customer Service, Road Safety",
        "speak" => "English, Hausa",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Chinedu",
        "last_name" => "Okoro",
        "email" => "chinedu.okoro@drivermail.com",
        "phone" => "+2348098765432",
        "password" => password_hash("Chine@2025#ok", PASSWORD_DEFAULT),
        "exp_years" => 4,
        "education" => "OND",
        "photo_path" => "https://randomuser.me/api/portraits/men/57.jpg",
        "nin" => "NIN86866446889",
        "acc_num" => "2981540296",
        "acc_name" => "Chinedu Okoro",
        "bank_name" => "Access Bank",
        "skills" => "Time Management, Navigation",
        "speak" => "English, Igbo",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Grace",
        "last_name" => "Alabi",
        "email" => "gracealabi1992@gmail.com",
        "phone" => "+2347045671234",
        "password" => password_hash("G@raceAlabi25", PASSWORD_DEFAULT),
        "exp_years" => 5,
        "education" => "B.Sc",
        "photo_path" => "https://randomuser.me/api/portraits/women/62.jpg",
        "nin" => "NIN40111756324",
        "acc_num" => "4198557023",
        "acc_name" => "Grace Alabi",
        "bank_name" => "UBA",
        "skills" => "Defensive Driving, Route Planning",
        "speak" => "English",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Yusuf",
        "last_name" => "Ibrahim",
        "email" => "yusufdriver@gmail.com",
        "phone" => "+2348012345678",
        "password" => password_hash("Yu$Ib!2025", PASSWORD_DEFAULT),
        "exp_years" => 3,
        "education" => "HND",
        "photo_path" => "https://randomuser.me/api/portraits/men/21.jpg",
        "nin" => "NIN64520238590",
        "acc_num" => "2209193725",
        "acc_name" => "Yusuf Ibrahim",
        "bank_name" => "GTBank",
        "skills" => "Customer Service, Road Safety",
        "speak" => "English, Hausa",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Kelechi",
        "last_name" => "Eze",
        "email" => "kelechi.eze@transport.ng",
        "phone" => "+2348176543210",
        "password" => password_hash("KelEze@2025!", PASSWORD_DEFAULT),
        "exp_years" => 6,
        "education" => "B.Sc",
        "photo_path" => "https://randomuser.me/api/portraits/men/83.jpg",
        "nin" => "NIN58367374983",
        "acc_num" => "7736924851",
        "acc_name" => "Kelechi Eze",
        "bank_name" => "First Bank",
        "skills" => "Defensive Driving, Route Planning",
        "speak" => "English, Igbo",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Aminat",
        "last_name" => "Balogun",
        "email" => "aminat.balogun@gmail.com",
        "phone" => "+2348061122334",
        "password" => password_hash("AmiBalogun#25", PASSWORD_DEFAULT),
        "exp_years" => 2,
        "education" => "SSCE",
        "photo_path" => "https://randomuser.me/api/portraits/women/18.jpg",
        "nin" => "NIN99294716348",
        "acc_num" => "5410932847",
        "acc_name" => "Aminat Balogun",
        "bank_name" => "Zenith Bank",
        "skills" => "Time Management, Navigation",
        "speak" => "English, Yoruba",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Samuel",
        "last_name" => "Johnson",
        "email" => "samueljohnson@ridehub.com",
        "phone" => "+2348056789012",
        "password" => password_hash("SamJ@25!ride", PASSWORD_DEFAULT),
        "exp_years" => 10,
        "education" => "M.Sc",
        "photo_path" => "https://randomuser.me/api/portraits/men/12.jpg",
        "nin" => "NIN14567420938",
        "acc_num" => "6183029472",
        "acc_name" => "Samuel Johnson",
        "bank_name" => "Access Bank",
        "skills" => "Attention to Detail, Punctuality",
        "speak" => "English",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Deborah",
        "last_name" => "Nwankwo",
        "email" => "deb.nwankwo@carservice.com",
        "phone" => "+2348120099887",
        "password" => password_hash("Deb#Nwan2025", PASSWORD_DEFAULT),
        "exp_years" => 1,
        "education" => "OND",
        "photo_path" => "https://randomuser.me/api/portraits/women/36.jpg",
        "nin" => "NIN50839174620",
        "acc_num" => "1420384759",
        "acc_name" => "Deborah Nwankwo",
        "bank_name" => "UBA",
        "skills" => "Time Management, Navigation",
        "speak" => "English, Igbo",
        "drive" => "Manual, Long Distance"
    ],
    [
        "first_name" => "Ibrahim",
        "last_name" => "Sule",
        "email" => "ibrahim.sule92@mail.com",
        "phone" => "+2348187654321",
        "password" => password_hash("IbSu@25Pass!", PASSWORD_DEFAULT),
        "exp_years" => 6,
        "education" => "B.Sc",
        "photo_path" => "https://randomuser.me/api/portraits/men/49.jpg",
        "nin" => "NIN77823014756",
        "acc_num" => "3840291864",
        "acc_name" => "Ibrahim Sule",
        "bank_name" => "GTBank",
        "skills" => "Defensive Driving, Route Planning",
        "speak" => "English, Hausa",
        "drive" => "Manual, Long Distance"
    ]
];

foreach ($drivers as $driver) {
    $stmt = $conn->prepare("INSERT INTO drivers 
        (first_name, last_name, email, phone, password, exp_years, education, photo_path,
         nin, acc_num, acc_name, bank_name, skills, speak, drive)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssssssssss",
        $driver['first_name'],
        $driver['last_name'],
        $driver['email'],
        $driver['phone'],
        $driver['password'],
        $driver['exp_years'],
        $driver['education'],
        $driver['photo_path'],
        $driver['nin'],
        $driver['acc_num'],
        $driver['acc_name'],
        $driver['bank_name'],
        $driver['skills'],
        $driver['speak'],
        $driver['drive']
    );

    if ($stmt->execute()) {
        echo "✅ Inserted: {$driver['first_name']} {$driver['last_name']}<br>";
    } else {
        echo "❌ Error inserting {$driver['email']}: " . $stmt->error . "<br>";
    }

    $stmt->close();
}

$conn->close();
?>
