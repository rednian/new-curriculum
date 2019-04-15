<script type="text/javascript">
    var json,
        display,
        yns = 0,
        limit = 5,
        active_pl = 0,
        tblListCurr = $('table#table-list-curr'),
        publicCurID,
        year = [
          'First Year - First Semester',
          'First Year - Second Semester',
          'Second Year - First Semester',
          'Second Year - Second Semester',
          'Third Year - First Semester',
          'Third Year - Second Semester',
          'Forth Year - First Semester',
          'Forth Year - Second Semester',
          'Fifth Year - First Semester',
          'Fifth Year - Second Semester'
        ];

    $(document).ready(function () {

      $("li.page_menu").removeClass("active");

      $("#menu_curriculum").addClass("active");

      // tblListCurr = $("#table-list-curr").dataTable({
      //   destroy: true,
      //   "bLengthChange": false,
      //   "bFilter": true,
      //   "pageLength": 3 ,
      //   "pagingType": "simple"
      // });

      addCurriculum();

    var tblListCurr = $('table#table-list-curr').DataTable({
        ajax: '<?php echo base_url('curriculum/getCurriculumByUser'); ?>',
        pagingType: 'simple',
        lengthChange : false,
        // pageLength : 2,
        // filter: true,
        destroy : true,
        columns : [{'data':'program'}],
      initComplete : function(setting, json) {
          $.each(json, function(index, data) {
            $.each(data, function(index, data) {
              getCurriculumPerProg(data.program_id);

            });
          });
      }
      });
    });


    function addCurriculum() {
      $("#addNewCurriculumForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
          url: "<?php echo base_url('curriculum/setNewCUrriculum') ?>",
          data: $(this).serialize(),
          type: "POST",
          dataType: "json",
          success: function (data) {
            $('table#table-list-curr').DataTable().ajax.reload();
            if (data['result'] == true) {
              // loadCurrList();
              $("#addNewCurriculumForm button[type=reset]").trigger("click");
              showMessage('Success', 'New curriculum has been set successfully.', 'success');
            }
            else {
              showMessage('Error', 'Cannot set new curriculum. Please try again', 'error');
            }
          },
          error: function () {

          }
        });
      });
    }

    // loadCurrList();

    function searchCurriculum(key) {
        $('table#table-list-curr').DataTable().search(key).draw();
    }

    function loadCurrList() {
        $.ajax({
            url: "<?php echo base_url('curriculum/curriculumList') ?>",
            dataType: "json",
            success: function (data) {
                tblListCurr.fnClearTable();
                $.each(data, function (key, value) {
                    tblListCurr.fnAddData([value]);
                    getCurriculumPerProg(key);
                });
            },
            error: function () {

            }
        });
    }

    function getCurriculumPerProg(pl_id) {
        $.ajax({
      url: "<?php echo base_url('curriculum/showCurrPerProgram') ?>",
      data: {pl_id: pl_id},
      type: "GET",
      dataType: "json",
      success: function (data) {
        // var display = "";
        $.each(data, function (index, value) {

          display = "<li onclick=\"previewCurriculum(" + value.cur_id + ")\">\
          <span class=\"fa fa-chevron-right\"></span>\
          <a href=\"javascript:;\">Revised Curriculum Effectivity\
          <span class=\"lbl-sem\">" + value['eff_sem'] + "</span> " +
              "SY: <span class=\"lbl-sy\">  " + value['eff_sy'] + "</span>" +
              "</a>" +
              "</li>";

                    $(".curr-list-container .curr-list#listProg_" + pl_id).append(display);
                });
            },
            error: function () {

            }
        });
    }

    function showMoreCurr(pl_id) {

        if (active_pl == pl_id) {
            limit += 5;
        }
        else {
            limit = 5;
        }

        $.ajax({
            url: "<?php echo base_url('curriculum/showMoreCurr') ?>",
            data: {pl_id: pl_id, limit: limit},
            type: "GET",
            dataType: "json",
            success: function (data) {
                $.each(data, function (key, value) {
                    display = "<li onclick=\"previewCurriculum(" + value['cur_id'] + ")\">\
			                            <span class=\"fa fa-chevron-right\"></span> <a href=\"javascript:;\">" + value['cur_id'] + " Revised Curriculum Effectivity <span class=\"lbl-sem\">" + value['eff_sem'] + "</span> SY: <span class=\"lbl-sy\">" + value['eff_sy'] + "</span></a>\
			                        </li>";

                    $(".curr-list-container .curr-list#listProg_" + pl_id).append(display);
                });

            },
            error: function () {

            }
        });
        // console.log(pl_id);
        // console.log(limit);
        active_pl = pl_id;
    }


    // ------------------------------------ AND YEAR AND SEMISTER -------------------------------------------------//
    // var
    // var yns = 0;

    function addYearAndSemister() {

        if (yns <= 9) {
            var yearsem = year[yns];
            $.ajax({
                url: "<?php echo base_url('curriculum/add_sem_year') ?>",
                data: {ys: yearsem, tr: tr},
                type: "GET",
                dataType: "html",
                success: function (data) {
                    $('#year_sem_container').append(data);
                    yns++;
                    tr++;
                    $("select.js-example-basic-multiple").select2("destroy");
                    $("select.js-example-basic-multiple").select2();
                },
                error: function () {

                }
            });
        }
    }

    function removePreviousYS() {
        bootbox.confirm("Are you sure you want to remove Year and Semester?", function (result) {
            if (result == true) {
                yns--;
                var str = "#ys_" + year[yns];
                str.replace(/\s/g, '');
                $(str.replace(/\s/g, '')).remove();
            }
        });
    }

    $('#setCurriculumModal').on('shown.bs.modal', function () {
        selectProgram();
    });

    function selectProgram() {
        $("form#addNewCurriculumForm select[name=pl_id]").html("");
        $.ajax({
            url: "<?php echo base_url('curriculum/program_list') ?>",
            dataType: "json",
            success: function (data) {
                $("form#addNewCurriculumForm select[name=pl_id]").append("<option selected class='hide'>Select program ...</option>");
                $.each(data, function (key, value) {
                    $("form#addNewCurriculumForm select[name=pl_id]").append("<option abv='" + value['prog_abv'] + "' value='" + value['pl_id'] + "'>" + value['prog_name'] +'-'+value['prog_abv']+ "</option>");
                });
            },
            error: function () {

            }
        });
    }

    function getProgramMajor(pl_id) {
        $.ajax({
            url: "<?php echo base_url('curriculum/getProgramMajor') ?>",
            data: {pl_id: pl_id},
            type: "GET",
            dataType: "json",
            success: function (data) {
                $("form#addNewCurriculumForm input#txtMajor").val(data['major']);
            },
            error: function () {

            }
        });
    }


    // var publicCurID;

    function previewCurriculum(cur_id) {
      console.log(cur_id);
        publicCurID = cur_id;
        $.ajax({
            url: "<?php echo base_url('curriculum/curriculumPreview') ?>",
            data: {cur_id: cur_id},
            type: "GET",
            dataType: "html",
            success: function (data) {
                yns = 0;
                $("#currPreviewContainer").html(data);
                retrieveSySem(cur_id);

                $("#formSaveRevisionCurriculum").on("submit", function (e) {
                    e.preventDefault();
                    $.ajax({
                        url: "<?php echo base_url('curriculum/save_revision') ?>",
                        data: $(this).serialize(),
                        type: "POST",
                        dataType: "json",
                        success: function (data) {
                            if (data['result'] == true) {
                                previewCurriculum(publicCurID);
                                showMessage('Success', 'New curriculum has been set successfully.', 'success');
                            }
                            else {
                                showMessage('Error', 'Cannot set new curriculum. Please try again', 'error');
                            }
                        },
                        error: function () {
                        }
                    });
                });
            },
            error: function () {

            }
        });
    }

    var codeProgAbv = "";
    var codeSem = "";
    var codeSY = "";

    $(document).ready(function () {
        $("#addNewCurriculumForm select[name=pl_id]").on('change', function () {
            codeProgAbv = $('option:selected', this).attr('abv');
            generateCurrCode();
        });
        $("#addNewCurriculumForm select[name=eff_sy]").on('change', function () {
            var val = $(this).val();
            codeSY = val.substr(2, 2) + val.substr(7, 2);
            generateCurrCode();
        });
        $("#addNewCurriculumForm select[name=eff_sem]").on('change', function () {
            var val = $(this).val();
            if (val == "1st Semester") {
                codeSem = "SEM1";
            }
            else if (val == "2nd Semester") {
                codeSem = "SEM2";
            }
            generateCurrCode();
        });
    });

    function generateCurrCode() {
        var code = codeProgAbv + "-" + codeSY + "-" + codeSem;
        $("#addNewCurriculumForm input[name=c_code]").val(code);
    }

    function retrieveSySem(cur_id) {
        getSubjectTags();
        $.ajax({
            url: "<?php echo base_url('curriculum/getYearSem') ?>",
            data: {cur_id: cur_id},
            type: "GET",
            dataType: "json",
            success: function (data) {
                var display = "";
                $.each(data, function (key, value) {
                    display += value;
                    yns++;
                });
                $("#existing_ys_container").html(display);
                $("select.js-example-basic-multiple").select2();
            },
            error: function () {

            }
        });

    }

    // ADD SUBJECTS //
    var tr = 1;

    function addShSubject(con) {
      $.ajax({
        url: "<?php echo base_url('curriculum/add_subject') ?>",
        data: {tr: tr, con: con},
        type: "GET",
        dataType: "html",
        success: function (data) {
          $("#" + con + " table.table-curr").append(data);
          tr++;
          $("select.js-example-basic-multiple").select2('destroy');
          $("select.js-example-basic-multiple").select2();
        },
        error: function () {

        }
      });
    }

    function add_subject(con) {
        $.ajax({
            url: "<?php echo base_url('curriculum/add_subject') ?>",
            data: {tr: tr, con: con},
            type: "GET",
            dataType: "html",
            success: function (data) {
                $("#" + con + " table.table-curr").append(data);
                tr++;
                $("select.js-example-basic-multiple").select2('destroy');
                $("select.js-example-basic-multiple").select2();
            },
            error: function () {

            }
        });
    }

    function remove_subject(con, tr) {

      console.log('con ->'+con+', tr->'+tr);

        bootbox.confirm("Are you sure you want to remove subject?", function (result) {
            if (result) {
                var element = "#" + con + " table.table-curr tbody tr#" + tr;
                $(element).remove();
            }
        });
    }

    function cancelSave() {
        $("#currPreviewContainer").html("<p>Please select curriculum to view . . .</p>");
    }

    function setActiveInactiveCurriculum() {
        $.ajax({
            url: "<?php echo base_url('curriculum/setActiveInactive') ?>",
            data: {cur_id: publicCurID},
            type: "GET",
            dataType: "json",
            success: function (data) {
                if (data.result == true) {
                    $("button#btnSetActiveInactiveCurriculum").html(data.str + " Curriculum");
                    showMessage('Success', 'Curriculum status changed successfully.', 'success');
                }
                else {
                    showMessage('Error', 'Curriculum unable to change status successfully.', 'error');
                }
            },
            error: function () {

            }
        });
    }

    function getSubjectTags() {
        $.ajax({
            url: "<?php echo base_url('curriculum/getSubjectLoadTags') ?>",
            dataType: "json"
        }).done(function(data) {
          json = data;
        });
    }

</script>