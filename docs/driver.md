# 🚗 Driver Dashboard Specification

This document outlines the full structure and components of the **Driver Dashboard** for the Driver Booking Platform.

---

## 📌 Overview

The Driver Dashboard allows drivers to:
- Manage availability
- View and respond to bookings
- Track completed jobs
- Manage their profile and documents
- See ratings and platform notifications

---

## 🧱 Dashboard Layout

## 🧱 Dashboard Layout

+----------------------------------------------------------+
| 👋 Welcome Banner - Driver Name, Status, Availability |
+----------------------------------------------------------+
| 📢 Alerts/Notices (e.g. Pending approval, new booking) |
+----------------------------------------------------------+
| 📅 Upcoming Bookings | 📊 Quick Stats |
+----------------------------------------------------------+
| ⭐ Ratings & Reviews | 👤 Profile Snapshot |
+----------------------------------------------------------+
| 📁 Documents | 📲 Notifications | 
+----------------------------------------------------------+



---

## ✅ Components & Features

### 1. 👋 Welcome Header
- Driver name and profile picture
- Status badge (Approved / Pending)
- Availability toggle (Online/Offline)

---

### 2. 📢 Admin Notices / Alerts
- Approval updates
- New booking assigned
- Pending profile or document update reminders

---

### 3. 📅 Upcoming & New Bookings
- Booking ID
- Employer name & phone (optional)
- Pickup and drop-off location
- Trip time/date
- Booking status (`pending`, `confirmed`, `completed`, `cancelled`)
- Accept / Reject buttons

---

### 4. ✅ Availability Toggle
- Toggle availability status
- Updates `drivers.availability` field in DB

---

### 5. 🚘 Profile Summary
- Name, Email, Phone
- Driving license number
- Vehicle info: plate number, model, color
- Bank details for payouts
- "Edit Profile" button

---

### 7. 📊 Quick Stats
- Total bookings accepted
- Total jobs completed
- Average rating
- Earnings (if wallet is enabled)

---

### 8. 📁 Document Manager
- Uploaded documents with status:
  - License
  - NIN
  - Passport photo
- "Reupload" or "View" options

---

### 9. 📲 Notification Center (Optional)
- Admin messages or system updates
- Read/unread status

---

### 10. ⚙️ Settings
- Change password
- Logout
- Contact admin / support link

---

## 📂 Optional Tabs / Links

| Tab | Description |
|-----|-------------|
| 📜 Booking History | List of all past bookings with statuses |
| 💳 Payment History | Payment logs and payout confirmations |
| 🧾 Profile Edit | Update biodata, car, or bank info |

---

## 💡 Technologies

- **Frontend**: HTML + Bootstrap/Tailwind
- **Backend**: PHP (with session auth)
- **Database Tables**:
  - `users`
  - `drivers`
  - `bookings`
  - `ratings`
  - `notifications` (optional)

---

## 📎 Future Enhancements
- Real-time socket notifications
- Mobile app version
- In-dashboard messaging system

