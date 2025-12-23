@extends('portal.layout')

@section('title', '我的请假记录')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check"></i> 我的请假记录</h2>
    <a href="{{ route('portal.leaves.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> 申请请假
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>请假类型</th>
                        <th>开始日期</th>
                        <th>结束日期</th>
                        <th>天数</th>
                        <th>状态</th>
                        <th>申请时间</th>
                        <th width="150">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td>{{ $leave->id }}</td>
                        <td>
                            <span class="badge bg-info">{{ $leave->leave_type_text }}</span>
                        </td>
                        <td>{{ $leave->start_date->format('Y-m-d') }}</td>
                        <td>{{ $leave->end_date->format('Y-m-d') }}</td>
                        <td><strong>{{ $leave->days }}</strong> 天</td>
                        <td>
                            @if($leave->status === 'pending')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> 待审批
                                </span>
                            @elseif($leave->status === 'approved')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> 已批准
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> 已拒绝
                                </span>
                            @endif
                        </td>
                        <td>{{ $leave->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('portal.leaves.show', $leave) }}" class="btn btn-outline-info" title="查看">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($leave->status === 'pending')
                                    <a href="{{ route('portal.leaves.edit', $leave) }}" class="btn btn-outline-warning" title="编辑">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('portal.leaves.destroy', $leave) }}" method="POST" class="d-inline" onsubmit="return confirm('确定要删除该请假申请吗？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="删除">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">暂无请假记录</p>
                                <a href="{{ route('portal.leaves.create') }}" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> 申请请假
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
        <div class="mt-3">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>
</div>
@endsection



