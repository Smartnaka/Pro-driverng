flowchart TD
  %% === FRONTEND UI ===
  subgraph Frontend UI
    DF1[Driver Registration Page]
    DF2[Login Page]
    DF3[Driver Dashboard]
    DF4[Profile Completion Form]
    DF5[Toggle Availability]
    DF6[Booking Notification]
    DF7[Accept / Reject Booking]
    DF8[Job Status: In Progress / Completed]
  end

  %% === BACKEND LOGIC ===
  subgraph Backend PHP
    DBE1[POST /register.php (role = driver)]
    DBE2[Insert into users & drivers (status = pending)]
    DBE3[Admin Approves Driver]
    DBE4[POST /login.php → validate → session]
    DBE5[GET /dashboard.php]
    DBE6[POST /update-profile.php]
    DBE7[Update driver details]
    DBE8[POST /toggle-availability.php]
    DBE9[Update driver.availability]
    DBE10[Booking Assigned]
    DBE11[POST /booking-response.php]
    DBE12[Accept → Update booking.status = confirmed]
    DBE13[Reject → Update booking.status = rejected]
    DBE14[POST /complete-job.php]
    DBE15[Update booking.status = completed]
  end

  %% === DATABASE ===
  subgraph MySQL DB
    DDB1[users]
    DDB2[drivers]
    DDB3[bookings]
  end

  %% === FLOW CONNECTIONS ===
  DF1 --> DBE1 --> DBE2 --> DDB1 & DDB2
  DBE2 --> DBE3
  DF2 --> DBE4 --> DF3

  DF3 --> DF4 --> DBE6 --> DBE7 --> DDB2
  DF3 --> DF5 --> DBE8 --> DBE9 --> DDB2

  DBE10 --> DF6
  DF6 --> DF7 --> DBE11

  DBE11 -->|Accept| DBE12 --> DDB3
  DBE11 -->|Reject| DBE13 --> DDB3

  DF3 --> DF8 --> DBE14 --> DBE15 --> DDB3
