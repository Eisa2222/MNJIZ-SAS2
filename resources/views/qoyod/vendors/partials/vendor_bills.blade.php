<script></script>

<div class="tab-pane fade" id="vendor_bills">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-user text-warning me-2"></i>
            <h6 class="card-title mb-0">فواتير مشتريات المورد</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>

        <div class="card-body px-5">
            <div class=" mb-6">
                <div class="card-body">
                    <table class="table datatable-bills">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المرجع</th>
                                <th>تاريخ الإصدار</th>
                                <th>القيمة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">إجماليات:</th>
                                <th></th>
                                <th></th> 
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Pass the PHP array as JSON into your script: --}}
            <script>
                window.billsData = @json($bills);
                $(function() {
                    const table = $('.datatable-bills').DataTable({
                        data: window.billsData,
                        columns: [{
                                data: null,
                                render: (data, type, row, meta) => meta.row + 1 // row number
                            },
                            {
                                data: 'reference'
                            },
                            {
                                data: 'issue_date'
                            },
                            {
                                data: 'total',
                                render: $.fn.dataTable.render.number(',', '.', 2) // format 54,978.76
                            },
                            {
                                data: 'status'
                            }
                        ],
                        order: [
                            [2, 'desc']
                        ],
                        footerCallback: function(row, data, start, end, display) {
                            const api = this.api();

                            // 1) Sum of the “total” column over all pages:
                            const totalAll = api
                                .column(3)
                                .data()
                                .reduce((sum, val) => sum + parseFloat(val || 0), 0);

                            // 2) Count of bills:
                            const countAll = data.length;

                            // 3) Update footer cells:
                            //    - col(3) is the “total” column’s footer
                            //    - col(0) could show count, or you can target another cell
                            $(api.column(3).footer()).html(totalAll.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }));
                            $(api.column(0).footer()).html(`(${countAll} فواتير)`);
                        },
                        // any other options you need...
                    });
                });
            </script>

        </div>
    </div>
</div>
