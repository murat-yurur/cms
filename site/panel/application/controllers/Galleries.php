<?php

class Galleries extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        /** Setting viewFolder */
        $this->viewFolder = "galleries_v";

        if (!get_active_user())
            redirect(base_url("login"));

        /** Loading Models */
        $this->load->model("gallery_model");
        $this->load->model("image_model");
        $this->load->model("video_model");
        $this->load->model("file_model");
    }

    public function index()
    {
        $viewData = new stdClass();

        /** Taking all data from the table */
        $items = $this->gallery_model->get_all(
            array(), "rank ASC"
        );

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "list";
        $viewData->items = $items;

        /** Loading View */
        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function new_form()
    {
        $viewData = new stdClass();

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "add";

        /** Loading View */
        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function save()
    {
        /** Loading Form Validation Library */
        $this->load->library("form_validation");

        /** Setting validation rules */
        $this->form_validation->set_rules("title", "Galeri Adı", "trim|required");

        /** Translating validation error messages */
        $this->form_validation->set_message(
            array(
                "required" => "<b>{field}</b> alanı boş bırakılamaz..."
            )
        );

        /** Running Form Validation */
        $validate = $this->form_validation->run();

        /** If validation is successful */
        if ($validate) {

            $gallery_type = $this->input->post("gallery_type");
            $path = "uploads/$this->viewFolder/";
            $folder_name = "";

            if ($gallery_type == "image") {

                $folder_name = convertToSEO($this->input->post("title"));
                $path = "$path/images/$folder_name";

            } else if ($gallery_type == "file") {

                $folder_name = convertToSEO($this->input->post("title"));
                $path = "$path/files/$folder_name";
            }

            if ($gallery_type != "video") {

                if (!mkdir($path, 0755)) {

                    /** Set the notification as Error */
                    $alert = array(
                        "title" => "İşlem Başarısız",
                        "text" => "Galeri Üretilirken problem oluştu. (Yetki Hatası)",
                        "type" => "error"
                    );

                    /** Set the session data with result */
                    $this->session->set_flashdata("alert", $alert);

                    /** Redirect to Module's List Page */
                    redirect(base_url("galleries"));

                    die();
                }
            }

            /** Then start insert statement */
            $insert = $this->gallery_model->add(
                array(
                    "title" => $this->input->post("title"),
                    "gallery_type" => $this->input->post("gallery_type"),
                    "url" => convertToSEO($this->input->post("title")),
                    "folder_name" => $folder_name,
                    "rank" => 0,
                    "isActive" => 1,
                    "createdAt" => date("Y-m-d H:i:s")
                )
            );

            /** If insert statement is successful */
            if ($insert) {

                /** Set the notification as Success */
                $alert = array(
                    "type" => "success",
                    "title" => "İşlem Başarılı",
                    "text" => "Kayıt başarılı bir şekilde eklendi.."
                );

                /** If insert statement is unsuccessful */
            } else {

                /** Set the notification as Error */
                $alert = array(
                    "type" => "error",
                    "title" => "İşlem Başarısız",
                    "text" => "Kayıt işlemi esnasında bir sorun oluştu.."
                );

            }

            /** Set the session data with result */
            $this->session->set_flashdata("alert", $alert);

            /** Redirect to Module's List Page */
            redirect(base_url("galleries"));

            /** If validation is unsuccessful */
        } else {
            /** Then reloading view and show error messages below the inputs */
            $viewData = new stdClass();

            /** Defining data to be sent to view */
            $viewData->viewFolder = $this->viewFolder;
            $viewData->subViewFolder = "add";
            $viewData->form_error = true;

            /** Reloading view */
            $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
        }

    }

    public function update_form($id)
    {
        $viewData = new stdClass();

        /** Taking the specific row's data from the table */
        $item = $this->gallery_model->get(
            array(
                "id" => $id
            )
        );

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "update";
        $viewData->item = $item;

        /** Loading View */
        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function update($id, $gallery_type = "image", $oldFolderName = "")
    {
        /** Loading Form Validation Library */
        $this->load->library("form_validation");

        /** Setting validation rules */
        $this->form_validation->set_rules("title", "Galeri Adı", "trim|required");

        /** Translating validation error messages */
        $this->form_validation->set_message(
            array(
                "required" => "<b>{field}</b> alanı boş bırakılamaz..."
            )
        );

        /** Running Form Validation */
        $validate = $this->form_validation->run();

        /** If validation is successful */
        if ($validate) {

            $path = "uploads/$this->viewFolder/";
            $folder_name = "";

            if ($gallery_type == "image") {

                $folder_name = convertToSEO($this->input->post("title"));
                $path = "$path/images";

            } else if ($gallery_type == "file") {

                $folder_name = convertToSEO($this->input->post("title"));
                $path = "$path/files";
            }

            if ($gallery_type != "video") {

                if (!rename("$path/$oldFolderName", "$path/$folder_name")) {

                    /** Set the notification as Error */
                    $alert = array(
                        "title" => "İşlem Başarısız",
                        "text" => "Galeri Üretilirken problem oluştur. (Yetki Hatası)",
                        "type" => "error"
                    );

                    /** Set the session data with result */
                    $this->session->set_flashdata("alert", $alert);

                    /** Redirect to Module's List Page */
                    redirect(base_url("galleries"));
                    die();
                }
            }

            /** Then start update statement */
            $update = $this->gallery_model->update(
                array(
                    "id" => $id
                ),
                array(
                    "title" => $this->input->post("title"),
                    "folder_name" => $folder_name,
                    "url" => convertToSEO($this->input->post("title")),
                )
            );

            /** If update statement is successful */
            if ($update) {

                /** Set the notification as Success */
                $alert = array(
                    "type" => "success",
                    "title" => "İşlem Başarılı",
                    "text" => "Kayıt başarılı bir şekilde güncellendi.."
                );

                /** If update statement is Unsuccessful */
            } else {

                /** Set the notification as Error */
                $alert = array(
                    "type" => "error",
                    "title" => "İşlem Başarısız",
                    "text" => "Kayıt güncelleme işlemi esnasında bir sorun oluştu.."
                );

            }

            $this->session->set_flashdata("alert", $alert);

            /** Redirect to Module's List Page */
            redirect(base_url("galleries"));

            /** If validation is unsuccessful */
        } else {

            /** Then reload view and show error messages below the inputs */
            $viewData = new stdClass();

            /** Taking the specific row's data from the table */
            $item = $this->gallery_model->get(
                array(
                    "id" => $id
                )
            );

            /** Defining data to be sent to view */
            $viewData->viewFolder = $this->viewFolder;
            $viewData->subViewFolder = "update";
            $viewData->item = $item;
            $viewData->form_error = true;

            /** Reloading View */
            $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
        }

    }

    public function delete($id)
    {
        $gallery = $this->gallery_model->get(
            array(
                "id" => $id
            )
        );

        if ($gallery) {

            if ($gallery->gallery_type == "image")

                $path = "uploads/$this->viewFolder/images/$gallery->folder_name";

            elseif ($gallery->gallery_type == "file")

                $path = "uploads/$this->viewFolder/files/$gallery->folder_name";

            $delete_folder = rrmdir($path);

            if (!$delete_folder) {

                /** Set the notification as Error */
                $alert = array(
                    "type" => "error",
                    "title" => "İşlem Başarısız",
                    "text" => "Kayıt silme işlemi esnasında bir sorun oluştu.."
                );

                $this->session->set_flashdata("alert", $alert);

                /** Redirect to Module's List Page */
                redirect(base_url("galleries"));

                die();
            }

            /** Starting delete statement */
            $delete = $this->gallery_model->delete(
                array(
                    "id" => $id
                )
            );

            /** If Delete Statement is successful */
            if ($delete) {

                /** Set the notification as Success */
                $alert = array(
                    "type" => "success",
                    "title" => "İşlem Başarılı",
                    "text" => "Kayıt başarılı bir şekilde silindi.."
                );

                /** If delete statement is unsuccessful */
            } else {

                /** Set the notification as Error */
                $alert = array(
                    "type" => "error",
                    "title" => "İşlem Başarısız",
                    "text" => "Kayıt silme işlemi esnasında bir sorun oluştu.."
                );
            }

            $this->session->set_flashdata("alert", $alert);

            /** Redirect to Module's List Page */
            redirect(base_url("galleries"));
        }
    }

    public function isActiveSetter($id)
    {
        /** If the posted data is true then set the isActive variable's value 1 else set 0 */
        $isActive = ($this->input->post("data") === "true") ? 1 : 0;

        /** Update the isActive column with isActive varible's value */
        $this->gallery_model->update(
            array(
                "id" => $id
            ),
            array(
                "isActive" => $isActive
            )
        );
    }

    public function rankSetter()
    {
        /** Set the values of $data array with posted data */
        $data = $this->input->post("data");

        /** Parsing values of $data array and put into the $order array */
        parse_str($data, $order);

        /** Set the values $items array with $order array and set keys as 'ord' and values as 'rank' */
        $items = $order["ord"];

        /** Update all  */
        foreach ($items as $rank => $id) {

            $this->gallery_model->update(
                array(
                    "id" => $id,
                    "rank!=" => $rank
                ),
                array(
                    "rank" => $rank
                )
            );
        }
    }

    public function upload_form($id)
    {
        $viewData = new stdClass();

        /** Taking the specific row's data from galleriess table */
        $item = $this->gallery_model->get(
            array(
                "id" => $id
            )
        );

        $viewData->item = $item;

        if ($item->gallery_type == "image") {
            /** Taking all images of a specific parent from the child table */
            $items = $this->image_model->get_all(
                array(
                    "gallery_id" => $id
                ), "rank ASC"
            );
        } elseif ($item->gallery_type == "file") {
            /** Taking all files of a specific parent from the child table */
            $items = $this->file_model->get_all(
                array(
                    "gallery_id" => $id
                ), "rank ASC"
            );
        } else {
            /** Taking all videos of a specific parent from the child table */
            $items = $this->video_model->get_all(
                array(
                    "gallery_id" => $id
                ), "rank ASC"
            );
        }

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "image";
        $viewData->items = $items;
        $viewData->gallery_type = $item->gallery_type;

        /** Loading View */
        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function file_upload($gallery_id, $gallery_type, $folder_name)
    {
        /** Taking the name of uploaded file */
        $file_name = convertToSEO(pathinfo($_FILES["file"]["name"], PATHINFO_FILENAME)) . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

        /** CodeIgniter 'Upload Library's configuration set */
        $config["allowed_types"] = ($gallery_type == "image") ? "jpg|jpeg|png" : "*";
        $config["upload_path"] = ($gallery_type == "image") ? "uploads/{$this->viewFolder}/images/$folder_name" : "uploads/{$this->viewFolder}/files/$folder_name";
        $config["file_name"] = $file_name;

        /** Load CodeIgniter 'Upload Library' */
        $this->load->library("upload", $config);

        /** Doing upload by 'do_upload' method */
        $upload = $this->upload->do_upload("file");

        /** If Upload Process is successful */
        if ($upload) {
            /** Create a Variable and set with Uploaded File's name */
            $uploaded_file = $this->upload->data("file_name");

            $modelName = ($gallery_type == "image") ? "image_model" : "file_model";

            /** Insert reference records to child table for uploaded images */
            $this->$modelName->add(
                array(
                    "url" => "{$config["upload_path"]}/$uploaded_file",
                    "rank" => 0,
                    "isActive" => 1,
                    "createdAt" => date("Y-m-d H:i:s"),
                    "gallery_id" => $gallery_id
                )
            );

            /** If Upload Process is Unsuccesful */
        } else {
            /** Set alert with error message */
            echo "aktarım başarısız";
        }
    }

    public function refresh_file_list($gallery_id, $gallery_type)
    {
        $viewData = new stdClass();

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "image";

        $modelName = ($gallery_type == "image") ? "image_model" : "file_model";

        /** Taking all images of a specific galleries from the images table */
        $viewData->items = $this->$modelName->get_all(
            array(
                "gallery_id" => $gallery_id
            )
        );

        $viewData->gallery_type = $gallery_type;

        /** Reload Render Element View */
        $render_html = $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/render_elements/file_list_v", $viewData, true);

        echo $render_html;
    }

    public function fileDelete($id, $parent_id, $gallery_type)
    {
        $modelName = ($gallery_type == "image") ? "image_model" : "file_model";

        /** Taking the specific row's data from galleriess table */
        $fileName = $this->$modelName->get(
            array(
                "id" => $id
            )
        );

        /** Starting Delete Statement */
        $delete = $this->$modelName->delete(
            array(
                "id" => $id
            )
        );

        /** If Image Delete Statement is successful */
        if ($delete) {

            /** Then deleting the file physically from disk */
            unlink($fileName->url);

            /** Set the notification as Success */
            $alert = array(
                "type" => "success",
                "title" => "İşlem Başarılı",
                "text" => "Görsel başarılı bir şekilde silindi.."
            );

            /** If Image Delete Statement is Unsuccessful */
        } else {

            /** Set the notification as Error */
            $alert = array(
                "type" => "error",
                "title" => "İşlem Başarısız",
                "text" => "Görsel silme işlemi esnasında bir sorun oluştu.."
            );

        }

        $this->session->set_flashdata("alert", $alert);

        /** Redirect to Module's List Page */
        redirect(base_url("galleries/upload_form/$parent_id"));
    }

    public function fileDeleteAll($parent_id, $gallery_type)
    {
        $modelName = ($gallery_type == "image") ? "image_model" : "file_model";

        /** Taking the specific row's data from galleriess table */
        $files = $this->$modelName->get_all(
            array(
                "gallery_id" => $parent_id
            )
        );

        /** Starting Delete Statement */
        $deleteAll = $this->$modelName->delete(
            array(
                "gallery_id" => $parent_id
            )
        );

        /** If Image Delete Statement is successful */
        if ($deleteAll) {

            /** Deleting files physically from disk */
            foreach ($files as $file) {
                unlink($file->url);
            }

            /** Set the notification as Success */
            $alert = array(
                "type" => "success",
                "title" => "İşlem Başarılı",
                "text" => "Tüm görseller başarılı bir şekilde silindi.."
            );

            /** If Image Delete Statement is Unsuccessful */
        } else {

            /** Set the notification as Error */
            $alert = array(
                "type" => "error",
                "title" => "İşlem Başarısız",
                "text" => "Görsel silme işlemi esnasında bir sorun oluştu.."
            );
        }

        $this->session->set_flashdata("alert", $alert);

        /** Redirect to Module's List Page */
        redirect(base_url("galleries/upload_form/$parent_id"));
    }

    public function fileIsActiveSetter($id, $gallery_type)
    {
        if ($id && $gallery_type) {

            $modelName = ($gallery_type == "image") ? "image_model" : "file_model";

            /** If the posted data is true then set the isActive variable's value 1 else set 0 */
            $isActive = ($this->input->post("data") === "true") ? 1 : 0;

            /** Update the isActive column with isActive varible's value */
            $this->$modelName->update(
                array(
                    "id" => $id,
                ),
                array(
                    "isActive" => $isActive
                )
            );
        }
    }

    public function fileRankSetter($gallery_type)
    {
        /** Set the values of $data array with posted data */
        $data = $this->input->post("data");

        /** Parsing values of $data array and put into the $order array */
        parse_str($data, $order);

        /** Set the values $images array with $order array and set keys as 'ord' and values as 'rank' */
        $items = $order["ord"];

        $modelName = ($gallery_type == "image") ? "image_model" : "file_model";

        /** Update all records of the table for every index of $images array if there is a difference for rank column */
        foreach ($items as $rank => $id) {
            $this->$modelName->update(
                array(
                    "id" => $id,
                    "rank!=" => $rank
                ),
                array(
                    "rank" => $rank
                )
            );
        }
    }

    public function gallery_video_list($id)
    {
        $viewData = new stdClass();

        $gallery = $this->gallery_model->get(
            array(
                "id" => $id
            )
        );

        /** Taking all data from the table */
        $items = $this->video_model->get_all(
            array(
                "gallery_id" => $id
            ), "rank ASC"
        );

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "video/list";
        $viewData->items = $items;
        $viewData->gallery = $gallery;

        /** Loading View */
        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function new_gallery_video_form($id)
    {
        $viewData = new stdClass();

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "video/add";
        $viewData->gallery_id = $id;

        /** Loading View */
        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function gallery_video_save($id)
    {
        /** Loading Form Validation Library */
        $this->load->library("form_validation");

        /** Setting validation rules */
        $this->form_validation->set_rules("url", "Video URL", "trim|required");

        /** Translating validation error messages */
        $this->form_validation->set_message(
            array(
                "required" => "<b>{field}</b> alanı boş bırakılamaz..."
            )
        );

        /** Running Form Validation */
        $validate = $this->form_validation->run();

        /** If validation is successful */
        if ($validate) {

            /** Then start insert statement */
            $insert = $this->video_model->add(
                array(
                    "url" => $this->input->post("url"),
                    "gallery_id" => $id,
                    "rank" => 0,
                    "isActive" => 1,
                    "createdAt" => date("Y-m-d H:i:s")
                )
            );

            /** If insert statement is successful */
            if ($insert) {

                /** Set the notification as Success */
                $alert = array(
                    "type" => "success",
                    "title" => "İşlem Başarılı",
                    "text" => "Kayıt başarılı bir şekilde eklendi.."
                );

                /** If insert statement is unsuccessful */
            } else {

                /** Set the notification as Error */
                $alert = array(
                    "type" => "error",
                    "title" => "İşlem Başarısız",
                    "text" => "Kayıt işlemi esnasında bir sorun oluştu.."
                );

            }

            /** Set the session data with result */
            $this->session->set_flashdata("alert", $alert);

            /** Redirect to Module's List Page */
            redirect(base_url("galleries/gallery_video_list/$id"));

            /** If validation is unsuccessful */
        } else {
            /** Then reloading view and show error messages below the inputs */
            $viewData = new stdClass();

            /** Defining data to be sent to view */
            $viewData->viewFolder = $this->viewFolder;
            $viewData->subViewFolder = "video/add";
            $viewData->form_error = true;
            $viewData->gallery_id = $id;

            /** Reloading view */
            $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
        }

    }

    public function update_gallery_video_form($id)
    {
        $viewData = new stdClass();

        /** Taking the specific row's data from the table */
        $item = $this->video_model->get(
            array(
                "id" => $id
            )
        );

        /** Defining data to be sent to view */
        $viewData->viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "video/update";
        $viewData->item = $item;

        /** Loading View */
        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function gallery_video_update($id, $gallery_id)
    {
        /** Loading Form Validation Library */
        $this->load->library("form_validation");

        /** Setting validation rules */
        $this->form_validation->set_rules("url", "Video URL", "trim|required");

        /** Translating validation error messages */
        $this->form_validation->set_message(
            array(
                "required" => "<b>{field}</b> alanı boş bırakılamaz..."
            )
        );

        /** Running Form Validation */
        $validate = $this->form_validation->run();

        /** If validation is successful */
        if ($validate) {

            /** Then start update statement */
            $update = $this->video_model->update(
                array(
                    "id" => $id
                ),
                array(
                    "url" => $this->input->post("url"),
                )
            );

            /** If update statement is successful */
            if ($update) {

                /** Set the notification as Success */
                $alert = array(
                    "type" => "success",
                    "title" => "İşlem Başarılı",
                    "text" => "Kayıt başarılı bir şekilde güncellendi.."
                );

                /** If update statement is Unsuccessful */
            } else {

                /** Set the notification as Error */
                $alert = array(
                    "type" => "error",
                    "title" => "İşlem Başarısız",
                    "text" => "Kayıt güncelleme işlemi esnasında bir sorun oluştu.."
                );

            }

            $this->session->set_flashdata("alert", $alert);

            /** Redirect to Module's List Page */
            redirect(base_url("galleries/gallery_video_list/$gallery_id"));

            /** If validation is unsuccessful */
        } else {

            /** Then reload view and show error messages below the inputs */
            $viewData = new stdClass();

            /** Taking the specific row's data from the table */
            $item = $this->video_model->get(
                array(
                    "id" => $id
                )
            );

            /** Defining data to be sent to view */
            $viewData->viewFolder = $this->viewFolder;
            $viewData->subViewFolder = "video/update";
            $viewData->item = $item;
            $viewData->form_error = true;

            /** Reloading View */
            $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
        }

    }

    public function galleryVideoDelete($id, $gallery_id)
    {
        /** Starting delete statement */
        $delete = $this->video_model->delete(
            array(
                "id" => $id,
                "gallery_id" => $gallery_id
            )
        );

        /** If Delete Statement is successful */
        if ($delete) {

            /** Set the notification as Success */
            $alert = array(
                "type" => "success",
                "title" => "İşlem Başarılı",
                "text" => "Kayıt başarılı bir şekilde silindi.."
            );

            /** If delete statement is unsuccessful */
        } else {

            /** Set the notification as Error */
            $alert = array(
                "type" => "error",
                "title" => "İşlem Başarısız",
                "text" => "Kayıt silme işlemi esnasında bir sorun oluştu.."
            );
        }

        $this->session->set_flashdata("alert", $alert);

        /** Redirect to Module's List Page */
        redirect(base_url("galleries/gallery_video_list/$gallery_id"));
    }

    public function galleryVideoDeleteAll($gallery_id)
    {
        /** Starting delete statement */
        $delete = $this->video_model->delete(
            array(
                "gallery_id" => $gallery_id
            )
        );

        /** If Delete Statement is successful */
        if ($delete) {

            /** Set the notification as Success */
            $alert = array(
                "type" => "success",
                "title" => "İşlem Başarılı",
                "text" => "Kayıt başarılı bir şekilde silindi.."
            );

            /** If delete statement is unsuccessful */
        } else {

            /** Set the notification as Error */
            $alert = array(
                "type" => "error",
                "title" => "İşlem Başarısız",
                "text" => "Kayıt silme işlemi esnasında bir sorun oluştu.."
            );
        }

        $this->session->set_flashdata("alert", $alert);

        /** Redirect to Module's List Page */
        redirect(base_url("galleries/gallery_video_list/$gallery_id"));
    }

    public function galleryVideoIsActiveSetter($id)
    {
        /** If the posted data is true then set the isActive variable's value 1 else set 0 */
        $isActive = ($this->input->post("data") === "true") ? 1 : 0;

        /** Update the isActive column with isActive varible's value */
        $this->video_model->update(
            array(
                "id" => $id
            ),
            array(
                "isActive" => $isActive
            )
        );
    }

    public function rankGalleryVideoSetter()
    {
        /** Set the values of $data array with posted data */
        $data = $this->input->post("data");

        /** Parsing values of $data array and put into the $order array */
        parse_str($data, $order);

        /** Set the values $items array with $order array and set keys as 'ord' and values as 'rank' */
        $items = $order["ord"];

        /** Update all  */
        foreach ($items as $rank => $id) {

            $this->video_model->update(
                array(
                    "id" => $id,
                    "rank!=" => $rank
                ),
                array(
                    "rank" => $rank
                )
            );
        }
    }

}