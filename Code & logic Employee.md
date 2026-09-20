# Tóm tắt triển khai chức năng Employee

## 1. Phạm vi

Tài liệu này tóm tắt các chức năng đã triển khai cho role `Employee` trong ứng dụng Laravel HRMS. Luồng nghiệp vụ được đối chiếu theo tài liệu `Hệ thống HRMs_v0.docx`, trong đó Employee có các nhóm chức năng:

- Đăng nhập, đăng xuất.
- Check-in, Check-out và trạng thái ca làm việc.
- Dashboard cá nhân.
- Xem và cập nhật tiến trình công việc được giao.
- Xem và cập nhật thông tin cá nhân theo phạm vi cho phép.
- Tạo, xem, sửa, hủy Phiếu yêu cầu/PYC của chính mình.

Kiến trúc hiện tại được giữ theo Laravel MVC + Blade. Không thêm API hoặc SPA.

## 2. Luồng đăng nhập và bắt buộc Check-in

```text
Employee đăng nhập
        |
        v
Có bản ghi attendance hôm nay đã Check-in?
        |                         |
       Không                      Có
        |                         |
        v                         v
Hiện modal bắt buộc          Cho vào workspace
Check-in                     Employee bình thường
```

Logic:

1. Sau khi đăng nhập, Employee được chuyển tới khu vực Employee.
2. Nếu hôm nay chưa có attendance với `check_in_at`, Dashboard hiển thị modal bắt buộc Check-in.
3. Middleware `EnsureEmployeeCheckedIn` chặn các màn hình Employee khác khi chưa Check-in.
4. Route Check-in vẫn được phép đi qua middleware để tránh vòng lặp redirect.
5. Mỗi Employee chỉ tạo một bản ghi attendance cho một ngày.

File chính:

- `app/Http/Middleware/EnsureEmployeeCheckedIn.php`
- `app/Http/Controllers/AttendanceCheckInController.php`
- `app/Http/Controllers/RoleHomeController.php`
- `resources/views/employee-dashboard.blade.php`

## 3. Luồng Check-out và Đăng xuất

```text
Employee đã Check-in
        |
        +--> Bấm nút Check-out
        |        |
        |        v
        |   Ghi check_out_at
        |   Hiện trạng thái Đã Check-out
        |
        +--> Bấm Đăng xuất khi chưa Check-out
                 |
                 v
          Hiện popup bắt buộc Check-out
                 |
                 v
          Check-out thành công
                 |
                 v
          Cho phép bấm Đăng xuất
```

Quy tắc đã triển khai:

- Check-out là thao tác thủ công, không tự động ghi nhận khi bấm Đăng xuất.
- Nếu Employee chưa Check-out mà bấm Đăng xuất, `LogoutController` trả về màn hình xác nhận Check-out và giữ nguyên session.
- Sau khi Check-out thành công, hệ thống quay lại Dashboard và hiển thị nút Đăng xuất.
- Nếu attendance đã có `check_out_at`, Đăng xuất được thực hiện trực tiếp.
- Nếu đã Check-out, nút trên Dashboard chuyển thành `Đã Check-out` và bị disabled.
- Backend không cho phép Check-out lần hai hoặc ghi đè thời gian Check-out cũ.
- Check-out không được xảy ra trước Check-in.

File chính:

- `app/Http/Controllers/Auth/LogoutController.php`
- `app/Http/Controllers/AttendanceCheckInController.php`
- `resources/views/attendance/check-out-confirmation.blade.php`
- `resources/views/employee-dashboard.blade.php`

Route chính:

```text
POST /attendance/check-in       attendance.check-in.store
POST /attendance/check-out      attendance.check-out
POST /logout                    logout
```

## 4. Dashboard Employee

Dashboard lấy dữ liệu thật theo Employee đang đăng nhập, không dùng số liệu mock cho các phần đã nối BE.

Các dữ liệu hiện có:

- Trạng thái attendance hôm nay.
- Giờ Check-in và Check-out.
- Tổng số công việc của Employee.
- Số công việc đến hạn hôm nay.
- Danh sách công việc gần đây.
- Danh sách PYC gần đây của Employee.
- Số PYC đang ở trạng thái `pending`.
- Thông tin phòng ban, chức vụ, mã nhân viên và email.

Logic lấy dữ liệu trong `RoleHomeController::employee()`:

```php
$attendance = $employee->attendances()
    ->whereDate('work_date', today())
    ->first();

$tasks = $employee->tasks()
    ->with('creator')
    ->orderByRaw('due_at IS NULL')
    ->orderBy('due_at')
    ->limit(4)
    ->get();

$requests = EmployeeRequest::where('created_by', $employee->user_id)
    ->latest()
    ->limit(3)
    ->get();
```

Lưu ý hiện tại:

- Panel công việc đang lấy tối đa 4 công việc gần nhất.
- Panel PYC đang lấy tối đa 3 PYC gần nhất.
- Nhãn `1 mục` hoặc `1 phiếu mới nhất` là số bản ghi thật được trả về, không phải giá trị hardcode.
- Yêu cầu đổi cả hai panel sang tối đa 5 bản ghi gần nhất chưa được triển khai trong phiên bản hiện tại.

File chính:

- `app/Http/Controllers/RoleHomeController.php`
- `resources/views/employee-dashboard.blade.php`
- `resources/views/hrms-layout.blade.php`
- `resources/css/app.css`

## 5. Quản lý công việc

Employee chỉ thao tác trên công việc được giao cho chính mình.

Chức năng:

- Xem danh sách công việc.
- Tìm theo tiêu đề.
- Lọc theo trạng thái.
- Lọc theo khoảng hạn hoàn thành.
- Xem chi tiết công việc.
- Cập nhật trạng thái.
- Cập nhật ghi chú.

Trạng thái được dùng:

```text
in_progress
completed
stopped
```

Logic phân quyền:

```php
abort_unless(
    $request->user()->employee?->id === $task->assigned_to,
    403
);
```

Employee không được xem hoặc cập nhật task của Employee khác.

Route:

```text
GET  /employee/tasks
GET  /employee/tasks/{task}
PUT  /employee/tasks/{task}/status
```

File chính:

- `app/Http/Controllers/EmployeeTaskController.php`
- `resources/views/employee-tasks.blade.php`
- `resources/views/employee-task-show.blade.php`

Chưa triển khai việc Employee tự tạo task hoặc xóa task, vì tài liệu chưa quy định rõ quyền tạo/xóa theo cấp bậc tổ chức.

## 6. Hồ sơ cá nhân

Employee được xem:

- Họ tên.
- Mã nhân viên.
- Email.
- Số điện thoại.
- Địa chỉ.
- Phòng ban.
- Chức vụ.
- Ngày vào làm.
- Trạng thái làm việc.

Employee được cập nhật trực tiếp các trường liên hệ:

- `phone`
- `email`
- `address`
- `avatar`

Các trường không được cập nhật trực tiếp:

- Mã nhân viên.
- Vai trò.
- Phòng ban.
- Chức vụ.
- Ngày vào làm.
- Trạng thái nhân sự.
- Ngày sinh.
- Giới tính.
- CCCD.

Ngày sinh, giới tính và CCCD được gửi qua PYC loại `profile_change`. PYC còn `pending` thì chưa cập nhật trực tiếp vào hồ sơ.

File chính:

- `app/Http/Controllers/EmployeeProfileController.php`
- `resources/views/employee-profile.blade.php`

Route:

```text
GET  /employee/profile
PUT  /employee/profile
POST /employee/profile/change-request
PUT  /employee/profile/change-request/{requestModel}
```

## 7. Phiếu yêu cầu/PYC

Các loại PYC hỗ trợ được định nghĩa trong `RequestType`:

```text
hardware
software
account
other
profile_change
```

PYC hỗ trợ thông thường trong form Employee gồm:

- Phần cứng.
- Phần mềm.
- Account.
- Khác.

`profile_change` chỉ được tạo từ luồng thay đổi hồ sơ, không cho chọn để bypass form hồ sơ.

Trạng thái PYC:

```text
pending
completed
rejected
```

Employee có thể:

- Tạo PYC.
- Xem danh sách PYC của chính mình.
- Xem chi tiết PYC của chính mình.
- Sửa PYC khi còn `pending`.
- Hủy/xóa PYC khi còn `pending`.

Employee không thể:

- Xem PYC của Employee khác.
- Sửa PYC đã được xử lý.
- Hủy PYC đã được xử lý.
- Duyệt hoặc từ chối PYC.

Logic sở hữu PYC:

```php
abort_unless(
    $record->created_by === $request->user()->id,
    403
);
```

Dữ liệu PYC lưu trong bảng `requests`:

- `created_by`: user tạo PYC.
- `type`: loại PYC.
- `payload`: tiêu đề, nội dung hoặc dữ liệu thay đổi hồ sơ.
- `status`: trạng thái xử lý.
- `processed_by`: người xử lý.
- `processed_at`: thời điểm xử lý.
- `processing_note`: ghi chú xử lý.

Route:

```text
GET    /employee/requests
GET    /employee/requests/create
POST   /employee/requests
GET    /employee/requests/{requestModel}
GET    /employee/requests/{requestModel}/edit
PUT    /employee/requests/{requestModel}
DELETE /employee/requests/{requestModel}
```

File chính:

- `app/Http/Controllers/EmployeeRequestController.php`
- `app/Models/Request.php`
- `app/Enums/RequestType.php`
- `app/Enums/RequestStatus.php`
- `resources/views/employee-requests.blade.php`
- `resources/views/employee-request-form.blade.php`
- `resources/views/employee-request-show.blade.php`

## 8. Giao diện và điều hướng

Đã đồng bộ các màn hình Employee theo giao diện Stitch ở các phần:

- Sidebar Employee.
- Active menu theo từng màn hình.
- Header và avatar người dùng.
- Breadcrumb theo dạng:

```text
HỆ THỐNG NHÂN SỰ / tên màn hình hiện tại
```

- Dashboard summary cards.
- Bảng công việc và PYC gần đây.
- Card trạng thái attendance.
- Nút Check-out đặt riêng bên dưới card trạng thái.
- Khi đã Check-out, nút chuyển sang trạng thái disabled `Đã Check-out`.
- Nút Đăng xuất ở sidebar và trong modal Check-out.

## 9. Kiểm thử đã có

Nhóm test Employee hiện bao phủ:

- Đăng nhập và phân quyền.
- Bắt buộc Check-in.
- Không Check-in hai lần trong ngày.
- Check-out không xảy ra trước Check-in.
- Không Check-out hai lần.
- Logout khi ca đang mở phải hiện popup Check-out.
- Check-out không tự logout.
- Logout trực tiếp sau khi đã Check-out.
- Dashboard lấy dữ liệu thật và link Laravel.
- Nút Check-out disabled sau khi đã Check-out.
- Task ownership, filter, pagination, status và notes.
- PYC type/status, ownership, edit/cancel pending và khóa PYC đã xử lý.
- Profile update và profile-change request.

Kết quả verification gần nhất:

```text
51 tests passed
176 assertions
Blade view cache: passed
git diff --check: passed
```

## 10. Các điểm chưa triển khai hoặc cần quyết định

- Chưa đổi Dashboard từ tối đa 4 task và 3 PYC thành tối đa 5 task và 5 PYC.
- Employee chưa được tạo task hoặc xóa task vì tài liệu chưa định nghĩa rõ quyền này.
- Chưa tự động cập nhật hồ sơ khi PYC `profile_change` còn chờ duyệt.
- Các chức năng ngoài phạm vi Employee không được mở rộng trong phần triển khai này.
