<section class="stitch-panel" style="margin:20px 0">
    <h2>Chi tiết phụ cấp và tiền phạt</h2>
    <p class="penalty-muted">{{ $salary->actual_worked_days }} ngày công thực tế đủ chấm công vào/ra. Cùng loại: ưu tiên nhân viên → phòng ban → công ty, cấu hình mới nhất ở cùng phạm vi.</p>
    @foreach($salary->adjustment_details['warnings'] ?? [] as $warning)<div class="notice alert-error">{{ $warning }}</div>@endforeach
    <div class="stitch-table-wrap" style="margin-top:16px"><table class="stitch-table"><thead><tr><th>Khoản</th><th>Đơn giá</th><th>Số lượng</th><th>Nguồn / Ghi chú</th><th>Thành tiền</th></tr></thead><tbody>
    @foreach(['allowances' => 'Phụ cấp', 'penalties' => 'Tiền phạt'] as $group => $label)
        @foreach($salary->adjustment_details[$group] ?? [] as $line)
            <tr><td><strong>{{ $line['name'] }}</strong><br><small>{{ $label }}</small></td><td>{{ number_format($line['amount'], 0, ',', '.') }}đ</td><td>{{ $line['quantity'] }} {{ $line['unit'] }}</td><td style="white-space:normal;max-width:350px">{{ $line['source'] ?? 'Cấu hình phụ cấp' }}@if(!empty($line['reason']))<br>{{ $line['reason'] }}@endif</td><td>{{ $group === 'allowances' ? '+' : '−' }}{{ number_format($line['total'], 0, ',', '.') }}đ</td></tr>
        @endforeach
    @endforeach
    @if(empty($salary->adjustment_details['allowances']) && empty($salary->adjustment_details['penalties']))<tr><td colspan="5" class="stitch-empty">Không có khoản phụ cấp hoặc tiền phạt phát sinh.</td></tr>@endif
    </tbody></table></div>
    <div class="notice">Lương trước cộng/trừ: <strong>{{ number_format((float) $salary->salary_before_adjustments, 0, ',', '.') }}đ</strong> + Phụ cấp: <strong>{{ number_format((float) $salary->allowance_total, 0, ',', '.') }}đ</strong> − Tiền phạt: <strong>{{ number_format((float) $salary->penalty_total, 0, ',', '.') }}đ</strong> = Thực nhận: <strong>{{ number_format((float) $salary->total_salary, 0, ',', '.') }}đ</strong>. Thực nhận tối thiểu bằng 0.</div>
    <p class="penalty-muted">{{ ['draft' => 'Bản nháp: sẽ tính lại theo chấm công và cấu hình hiện tại.', 'confirmed' => 'Đã chốt: giữ nguyên số tiền và chi tiết tại thời điểm chốt.', 'paid' => 'Đã thanh toán: giữ nguyên số liệu.'][$salary->status->value] }}</p>
</section>
