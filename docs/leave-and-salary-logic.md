# Logic nghỉ phép và tính lương

Tài liệu này mô tả logic nghiệp vụ và các ràng buộc hiện tại của chức năng nghỉ phép, duyệt nghỉ phép và tính lương trong hệ thống HRMS.

## 1. Quy tắc ngày phép

- Mỗi nhân viên được hưởng tối đa **10 ngày phép trong một năm**.
- Nghỉ cả ngày được tính là `1 ngày phép`.
- Nghỉ nửa ngày được tính là `0,5 ngày phép`.
- Hai lần nghỉ nửa ngày tương đương một ngày phép.
- Chỉ các ngày từ **Thứ Hai đến Thứ Sáu** được tính là ngày làm việc.
- Thứ Bảy và Chủ Nhật không được tính vào số ngày nghỉ hoặc số ngày công.
- Đơn nghỉ phép chỉ được nằm trong cùng một năm để bảo đảm tính ngày phép chính xác.
- Đơn nghỉ nửa ngày chỉ được đăng ký trong một ngày.
- Khoảng thời gian nghỉ không được trùng với đơn đang chờ duyệt hoặc đơn đã được duyệt.
- Đơn chỉ gồm ngày cuối tuần sẽ không được tạo.

## 2. Trạng thái đơn nghỉ phép

### Đang chờ duyệt

- Nhân viên đã gửi đơn.
- Chưa trừ ngày phép.
- Chưa ảnh hưởng đến bảng lương.
- HR có thể duyệt hoặc từ chối.

### Đã duyệt

- Đơn bắt đầu được tính vào ngày phép đã sử dụng.
- Hệ thống phân bổ phần nghỉ được hưởng lương và phần nghỉ không lương.
- Đơn nghỉ trong số ngày phép được hưởng vẫn được tính lương bình thường.

### Từ chối

- Không trừ ngày phép.
- Không ảnh hưởng đến tiền lương.

Sau khi đơn đã được duyệt hoặc từ chối, đơn không được xử lý lại.

## 3. Phân bổ ngày phép khi HR duyệt

Khi HR duyệt đơn, hệ thống sắp xếp các đơn đã duyệt theo ngày bắt đầu nghỉ và phân bổ ngày phép theo thứ tự.

Ví dụ nhân viên đã sử dụng `9,5 ngày phép`:

- Đăng ký thêm `1 ngày`.
- `0,5 ngày` đầu được tính là ngày phép được hưởng.
- `0,5 ngày` còn lại là nghỉ vượt số ngày phép được hưởng.

HR vẫn có thể duyệt đơn vượt số ngày phép. Tuy nhiên, phần vượt sẽ được đánh dấu là **nghỉ không lương**.

## 4. Quy trình nghiệp vụ

```text
Nhân viên tạo đơn
        |
        v
Đơn chờ HR xử lý
        |
        +--> Từ chối: không trừ ngày phép, không ảnh hưởng lương
        |
        +--> Duyệt: phân bổ ngày phép có lương/không lương
                              |
                              v
                     Cập nhật bảng lương tháng
```

## 5. Tính lương

### Lương cứng

Mức lương cứng mẫu hiện tại là:

```text
15.000.000đ/tháng
```

### Số ngày công trong tháng

Số ngày công được tính bằng tổng số ngày từ Thứ Hai đến Thứ Sáu trong tháng.

Thứ Bảy, Chủ Nhật không được tính vào số ngày công.

### Lương ngày

```text
Lương ngày = Lương cứng tháng / Số ngày làm việc trong tháng
```

Ví dụ tháng có 22 ngày làm việc:

```text
Lương ngày     = 15.000.000 / 22 = 681.818,18đ
Lương nửa ngày = 681.818,18 / 2 = 340.909,09đ
```

### Tổng lương

```text
Tổng lương = Lương cứng tháng - (Ngày nghỉ không lương × Lương ngày)
```

Quy tắc cụ thể:

- Nghỉ phép đã duyệt trong 10 ngày phép: vẫn hưởng đủ lương.
- Nghỉ nửa ngày trong số ngày phép được hưởng: vẫn hưởng đủ lương của ngày đó.
- Nghỉ vượt ngày phép cả ngày: trừ `1 × lương ngày`.
- Nghỉ vượt ngày phép nửa ngày: trừ `0,5 × lương ngày`.
- Đơn đang chờ duyệt chưa được tính vào bảng lương.
- Đơn bị từ chối không được tính vào bảng lương.

## 6. Ví dụ tính lương

Giả sử:

- Lương cứng: `15.000.000đ`.
- Tháng có `22 ngày làm việc`.
- Nhân viên nghỉ vượt ngày phép `1,5 ngày`.

```text
Lương ngày       = 15.000.000 / 22 = 681.818,18đ
Tiền bị trừ      = 1,5 × 681.818,18 = 1.022.727,27đ
Tổng lương       = 15.000.000 - 1.022.727,27
                 = 13.977.272,73đ
```

## 7. Dữ liệu bảng `leave_requests`

Bảng `leave_requests` lưu các thông tin:

- Nhân viên gửi đơn.
- Người tạo đơn.
- Ngày bắt đầu và ngày kết thúc.
- Hình thức nghỉ: cả ngày hoặc nửa ngày.
- Buổi nghỉ: sáng hoặc chiều đối với đơn nửa ngày.
- Số ngày phép yêu cầu.
- Số ngày nghỉ có lương.
- Số ngày nghỉ không lương.
- Lý do nghỉ.
- Trạng thái đơn: chờ duyệt, đã duyệt hoặc từ chối.
- Người xử lý, thời gian xử lý và ghi chú của HR.

Chỉ các bản ghi có trạng thái `approved` mới được cộng vào số ngày phép đã sử dụng.

## 8. Dữ liệu bảng `monthly_salaries`

Bảng `monthly_salaries` lưu bảng lương theo từng nhân viên và từng tháng:

- Nhân viên.
- Năm và tháng.
- Lương cứng.
- Tổng số ngày làm việc trong tháng.
- Số ngày làm việc dự kiến sau khi trừ ngày nghỉ đã duyệt.
- Tổng số ngày nghỉ đã duyệt.
- Số ngày nghỉ có lương.
- Số ngày nghỉ không lương.
- Lương ngày.
- Tổng lương tháng.
- Trạng thái bảng lương.
- Thời điểm tính lương gần nhất.

Mỗi nhân viên chỉ có một bảng lương cho một tháng trong một năm.

## 9. Các màn hình hiện có

### Nhân viên

- `/employee/leave`: gửi đơn và xem lịch sử nghỉ phép.
- `/employee/leave/create`: tạo đơn nghỉ phép.
- `/employee/leave/{leaveRequest}`: xem chi tiết đơn.
- `/employee/salary`: xem bảng lương và lịch sử lương cá nhân.

### HR

- `/hr/leave`: xem lịch sử nghỉ phép của nhân viên và lọc theo trạng thái.
- `/hr/leave/{leaveRequest}`: xem chi tiết, duyệt hoặc từ chối đơn.
- `/hr/salaries`: xem bảng lương nhân viên theo tháng.

## 10. Giới hạn hiện tại và khả năng mở rộng

Hiện tại hệ thống:

- Tính lương theo ngày làm việc trong lịch, chưa trừ thêm theo dữ liệu check-in/check-out thực tế.
- Chưa tính ngày lễ quốc gia.
- Mức lương đang dùng mặc định là `15.000.000đ/tháng`.
- Đã có cột trạng thái bảng lương để mở rộng quy trình xác nhận hoặc thanh toán.
- Đã lưu riêng ngày nghỉ có lương và không lương để có thể mở rộng thêm phụ cấp, khấu trừ, thưởng và bảng lương chi tiết.
