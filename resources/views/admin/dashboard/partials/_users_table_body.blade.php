@forelse ($users as $user)
    <tr data-user-id="{{ $user->id }}" data-status="{{ $user->status }}">
        <td>{{ $user->email }}</td>
        <td>{{ $user->created_at->translatedFormat('d M Y') }}</td>
        <td>
            @if ($user->status == 1)
                <span class="status-tag active">Active</span>
            @else
                <span class="status-tag banned">Banned</span>
            @endif
        </td>
        <td class="action-buttons">
            @if ($user->status == 1)
                <button class="btn btn-sm btn-danger" data-action="ban">แบน</button>
            @else
                <button class="btn btn-sm btn-warning" data-action="unban">ปลดแบน</button>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" style="text-align: center; padding: 20px;">
            ไม่พบผู้ใช้งานที่ตรงกับคำค้นหา
        </td>
    </tr>
@endforelse