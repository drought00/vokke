@extends('layouts.default')

@section('content')
<div class="container">
    <div class="demo-container">
        <div class="mb-2">
            <button id="addKangarooBtn" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kangarooModal">
                Add Kangaroo
            </button>
        </div>
        <div id="gridContainer"></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let serviceUrl = "{{ env('VOKKE_API_ENDPOINT') }}/kangaroos";
        var customDataSource = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
                return $.getJSON(serviceUrl)
                    .fail(function() { throw "Data loading error" });
            }
        });

        $('#gridContainer').dxDataGrid({
            dataSource: customDataSource,
            columns: ['name', 'nickname', 'weight', 'height', 'gender', 'color', 'friendliness', 'birthday',
            {
                caption: 'Actions',
                cellTemplate: function(container, options) {
                    const editButton = $('<a class="me-2">Edit</a>');
                    const deleteButton = $('<a class="">Delete</a>');

                    editButton.appendTo(container);
                    deleteButton.appendTo(container);

                    editButton.on('click', function() {
                        const kangarooId = options.data.id;
                        const editUrl = `${serviceUrl}/${kangarooId}`;
                        $.ajax({
                            url: editUrl,
                            method: 'GET',
                            success: function(response) {
                                updateKangarooModal('edit', editUrl);
                                $('#kangarooModal').modal('show');
                                clearValidationErrors();
                                populateEditModal(response.data);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching kangaroo data:', error);
                            }
                        });
                    });

                    deleteButton.on('click', function() {
                        const kangarooId = options.data.id;
                        if (confirm('Are you sure you want to delete this kangaroo?')) {
                            const deleteUrl = `${serviceUrl}/${kangarooId}`;
                            $.ajax({
                                url: deleteUrl,
                                method: 'DELETE',
                                success: function(response) {
                                    console.log('Kangaroo deleted successfully:', response);
                                    showSuccessMessage('Kangaroo deleted successfully!');
                                    $("#gridContainer").dxDataGrid("instance").refresh();
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error deleting kangaroo:', error);
                                }
                            });
                        }
                    });
                }
            }],
            showBorders: true,
            remoteOperations: { groupPaging: true }
        });

        $('#addKangarooBtn').on('click', function() {
            $('#addKangarooForm')[0].reset();
            updateKangarooModal('add', '{{ env("VOKKE_API_ENDPOINT") }}/kangaroos');
            clearValidationErrors();
        });

        $('#saveKangarooBtn').on('click', function() {
            const formData = $('#addKangarooForm').serialize();
            url = $(this).data('url');
            method = $(this).data('method');
            clearValidationErrors();

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(data) {
                    showSuccessMessage(data.message);
                    $("#gridContainer").dxDataGrid("instance").refresh();
                },
                error: function(data) {
                    displayValidationErrors(data.responseJSON.error);
                }
            });
        });

        function displayValidationErrors(errorMessages) {
            for (const field in errorMessages) {
                if (errorMessages.hasOwnProperty(field)) {
                    const errorMessage = errorMessages[field].join('<br>');
                    const errorContainer = $(`#${field}Error`);
                    errorContainer.html(errorMessage);
                }
            }
        }

        function clearValidationErrors()
        {
            const errorMessagesDiv = $('.vokke-error-message');
            errorMessagesDiv.empty();
        }

        function showSuccessMessage(message) {
            const successAlert = $('<div class="alert alert-success" role="alert"></div>');
            successAlert.text(message);
            $('#addKangarooForm').prepend(successAlert);
            setTimeout(function() {
                successAlert.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
        }

        function populateEditModal(kangarooData) {
            $('#name').val(kangarooData.name);
            $('#nickname').val(kangarooData.nickname);
            $('#weight').val(kangarooData.weight);
            $('#height').val(kangarooData.height);
            $('#gender').val(kangarooData.gender);
            $('#color').val(kangarooData.color);
            $('#friendliness').val(kangarooData.friendliness);
            $('#birthday').val(kangarooData.birthday);
        }

        function updateKangarooModal(method, url)
        {
            $('#saveKangarooBtn').attr('data-url', url).data('url', url);
            if (method == "edit") {
                $('#modal-title').text('Edit Kangaroo');
                $('#saveKangarooBtn').attr('data-method', 'PUT').data('method', 'PUT');
            } else {
                $('#modal-title').text('Add Kangaroo');
                $('#saveKangarooBtn').attr('data-method', 'POST').data('method', 'POST');
            }
        }
    });
</script>
@endsection

@section('modal')
<div class="modal fade" id="kangarooModal" tabindex="-1" aria-labelledby="kangarooModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-title">Add Kangaroo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addKangarooForm">
                    <div class="form-group mb-2">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="text-danger vokke-error-message" id="nameError"></div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="nickname">Nickname</label>
                        <input type="text" class="form-control" id="nickname" name="nickname">
                        <div class="text-danger vokke-error-message" id="nicknameError"></div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="weight">Weight</label>
                        <input type="number" class="form-control" id="weight" name="weight" min="0" required>
                        <div class="text-danger vokke-error-message" id="weightError"></div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="height">Height</label>
                        <input type="number" class="form-control" id="height" name="height" min="0" required>
                        <div class="text-danger vokke-error-message" id="heightError"></div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="gender">Gender</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <div class="text-danger vokke-error-message" id="genderError"></div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="color">Color</label>
                        <input type="text" class="form-control" id="color" name="color">
                        <div class="text-danger vokke-error-message" id="colorError"></div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="friendliness">Friendliness</label>
                        <select class="form-control" id="friendliness" name="friendliness" required>
                            <option value="friendly">Friendly</option>
                            <option value="independent">Independent</option>
                        </select>
                        <div class="text-danger vokke-error-message" id="friendlinessError"></div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="birthday">Birthday</label>
                        <input type="date" class="form-control" id="birthday" name="birthday" required>
                        <div class="text-danger vokke-error-message" id="birthdayError"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveKangarooBtn" data-url='{{ env("VOKKE_API_ENDPOINT") }}/kangaroos' data-method='POST'>Save</button>
            </div>
        </div>
    </div>
</div>
@endsection