<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../include/BookingService.php';

class BookingServiceTest extends TestCase {
    public function testIsDuplicateBookingReturnsTrueIfBookingExists() {
        // Mock mysqli connection and statement
        $mockConn = $this->createMock(mysqli::class);
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('bind_param');
        $mockStmt->expects($this->once())->method('execute');
        $mockStmt->expects($this->once())->method('store_result');
        $mockStmt->method('__get')->with('num_rows')->willReturn(1);
        $mockStmt->expects($this->once())->method('close');

        $service = new BookingService($mockConn);
        $this->assertTrue($service->isDuplicateBooking('SOME_REF'));
    }

    public function testIsDuplicateBookingReturnsFalseIfNoBooking() {
        $mockConn = $this->createMock(mysqli::class);
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('bind_param');
        $mockStmt->expects($this->once())->method('execute');
        $mockStmt->expects($this->once())->method('store_result');
        $mockStmt->method('__get')->with('num_rows')->willReturn(0);
        $mockStmt->expects($this->once())->method('close');

        $service = new BookingService($mockConn);
        $this->assertFalse($service->isDuplicateBooking('SOME_REF'));
    }

    // More tests can be added for verifyPayment and createBooking with further mocking
} 