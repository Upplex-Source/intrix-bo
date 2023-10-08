<br>
<form action="{{ route( 'admin.fuel_expense.importFuelExpense' ) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="input-group">
        <input class="form-control" type="file" name="file">
        <button class="btn btn-sm btn-primary">Upload</button>
    </div>
</form>
<br>
<div>
    Click <strong><a href="{{ asset( 'admin/sample_excel/Fuel Expense.xlsx' ) }}" download>HERE</a></strong> to download the Sample Excel File
</div>