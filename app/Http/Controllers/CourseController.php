<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\CoursesModules;
use App\Models\CoursesUsers;
use App\Models\CoursesWarnings;
use App\Models\ModulesVideos;
use App\Models\VideosExercises;
use App\Models\VideosWatcheds;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    private function getClientLooged()
    {
        return Auth::user();
    }

    public function setNewCourse(Request $request)
    {     
        $array_return = ['error' => ''];

        //Rules and data
        $name =  htmlentities($request->input('name'));
        $description = $request->input('description');

        $rules_slug = [' ', '/', '@', '*', ':', ';', '?', '&', '$', '#'];
        $slug = str_replace($rules_slug, "-", $name);
        $slug = strtolower($slug);

        //Check disponible slug
        if(Courses::where('slug', $slug)->first()) {
            return [
                'error' => 'Este nome de curso já existe' 
            ];
        }

        //Save file
        if(isset($_FILES['file'])) {
            $arquivo = $_FILES['file'];
            $extension = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            $name_file = $this->getClientLooged()->id.'@'.rand(615, 5173179).rand(1, 5342).rand(19, 173528).rand(182, 525382);

            move_uploaded_file($arquivo['tmp_name'], 'files/'.$name_file.'.'.$extension);
        } else {
            return ['error' => 'Envie uma imagem'];
        } 

        if(!$name) return ['error' => 'Preencha o nome do curso'];

        //Save in DB
        $save = new Courses();
        $save->id_user = $this->getClientLooged()->id;
        $save->name = $name;
        $save->slug = $slug;
        $save->description = $description;
        $save->imagem = asset('files'.'/'.$name_file.'.'.$extension);
        $save->save();

        return $array_return;
    }


    public function setNewCourseWarning(Request $request, Int $id_course) 
    {
        $array_return = ['error' => ''];

        //Query of course by user
        $query = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $id_course)->first();

        if(!$query) return ['error' => 'Curso não encontrado'];

        //Data of request
        $title = $request->input('title');
        $body = $request->input('body');

        if($title && $body) {
            $save = new CoursesWarnings();
            $save->id_course = $id_course;
            $save->title = $title;
            $save->body = $body;
            $save->save();
        } else {
            return ['error' => 'Envie todos os campos'];
        }

        return $array_return;
    }


    public function setDelCourseWarning (Int $id_warning)
    {
        $query = CoursesWarnings::find($id_warning);

        if(!$query) return ['error' => 'Erro alerta not found - 404'];

        //Check if the course is user
        $check_course = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $query->id_course)->first();

        if($check_course) {
            $query->delete();
        } else {
            return ['error' => 'Este curso não é seu'];
        }

        return ['error' => ''];
    }

    public function setUserInCourseId(Int $id_course) 
    {
        $course = Courses::select('id')->find($id_course);

        if(!$course) return ['error' => 'Curso não encontrado'];

        $check = CoursesUsers::where('id_user', $this->getClientLooged()->id)->where('id_course', $id_course)->first();

        if(!$check) {
            $save = new CoursesUsers();
            $save->id_user = $this->getClientLooged()->id;
            $save->id_course = $id_course;
            $save->save();
        }

        return ['error' => ''];
    }

    public function getMyCourses()
    {
        $users_courses = CoursesUsers::where('id_user', $this->getClientLooged()->id)->get();

        $array_return = [];

        foreach($users_courses as $k => $u) {
            $course = Courses::where('id', $u->id_course)->first();

            $array_return[$k] = $course;
        }

        return $array_return;
    }

    public function getCourseUsingSlug(String $slug_course)
    {
        $course = Courses::select('id', 'name', 'description', 'imagem')->where('slug', $slug_course)->first();

        if(!$course) return ['error' => 'Curso não encontrado'];

        //Check course of user
        $check_course = CoursesUsers::where('id_user', $this->getClientLooged()->id)->where('id_course', $course->id)->count();

        if($check_course < 1) return ['error' => 'Você não tem este curso'];

        //Data of course
        $data = [
            'course' => $course
        ];

        $modules = CoursesModules::select('id', 'name')->where('id_course', $course->id)->get();

        foreach($modules as $mk => $mv) {
            $data['modules'][$mk] = $mv;
            
            //Videos
            $videos = ModulesVideos::where('id_module', $mv->id)->get();
            $data['modules'][$mk]['videos']  = $videos;

            foreach($videos as $ki => $vi) {
                //Checking if the video have one register in the table watched videos
                $check = VideosWatcheds::where('id_user', $this->getClientLooged()->id)->where('id_video', $vi->id)->first();

                if($check  ) {  
                        $data['modules'][$mk]['videos'][$ki]['video_watched']= true; 
                } else { 
                        $data['modules'][$mk]['videos'][$ki]['video_watched']= false; 
                }
            }
        }
        return $data;
    }

    public function getInfoCourse(String $slug_course) {
        $course = Courses::select('id', 'description')->where('slug', $slug_course)->first();

        if(!$course) return ['error' => 'Curso não encontrado'];

        //Check course of user
        $check_course = CoursesUsers::where('id_user', $this->getClientLooged()->id)->where('id_course', $course->id)->count();

        if($check_course < 1) return ['error' => 'Você não tem este curso'];

        //Get the data
        $data = [
            'course' => $course->description,
            'modules' => 0,
            'videos' => 0,
            'exercises' => 0 
        ];

        $data['modules'] = CoursesModules::where('id_course', $course->id)->count();
        $data['users'] = CoursesUsers::where('id_course', $course->id)->count(); 
        $modules = CoursesModules::where('id_course', $course->id)->get();

        foreach($modules as $k => $v) {
            $data['videos'] += ModulesVideos::where('id_module', $v->id)->count();

            $videos = ModulesVideos::select('id')->where('id_module', $v->id)->get();
           
        }
 
        foreach($videos as $vk => $vv) {
            $data['exercises'] = VideosExercises::where('id_video', $vv->id)->count();
        }

        return $data;
    }

    public function getCourseWarnings(String $slug_course) {
        $course = Courses::select('id', 'description')->where('slug', $slug_course)->first();

        if(!$course) return ['error' => 'Curso não encontrado'];

        //Check course of user
        $check_course = CoursesUsers::where('id_user', $this->getClientLooged()->id)->where('id_course', $course->id)->count();

        if($check_course < 1) return ['error' => 'Você não tem este curso'];

        $warnings = CoursesWarnings::where('id_course', $course->id)->get();

        foreach($warnings as $k => $v) {
            date_default_timezone_set('America/Sao_Paulo');

            $date  = new DateTime($v->date);
            $date2 = new DateTime(date('Y-m-d H:i:s'));
            $re = $date->diff($date2);

            if($re->h < 1 && $re->d < 1 && $re->m < 1 && $re->y === 0) {
                if($re->i < 1) {
                    $date = $re->s.' segundos atrás';
                } else {
                    if($re->i === 1) {
                        $date = $re->i.' minuto atrás';
                    } else {
                        $date = $re->i.' minutos atrás';
                    }
                }
            } else if($re->h >= 1 && $re->d < 1 && $re->m < 1  && $re->y === 0) {
                if($re->h === 1) {
                        $date = $re->h.' hora atrás';
                } else {
                        $date = $re->h.' horas atrás';
                }
            } else if($re->d >= 1 && $re->m < 1  && $re->y === 0) {
                    if($re->d === 1) {
                        $date = $re->d.' dia atrás';
                    } else {
                        $date = $re->d. ' dias atrás';
                    }
            } else if($re->m < 1  && $re->y === 0) {
                    if($re->d === 1) {
                        $date = $re->d.' dia atrás';
                    } else {
                        $date = $re->d. ' dias atrás';
                    }
            }  else if($re->m > 1  && $re->y === 0) {
                    if($re->m === 1) {
                        $date = $re->m.' mês atrás';
                    } else {
                        $date = $re->m.' meses atrás';
                    }
            } else if($re->y >= 1) {
                    if($re->y === 1) {
                        $date = $re->y.' ano atrás';
                    } else {
                        $date = $re->y.' anos atrás';
                    }
            }

            $warnings[$k]->date = $date;
        }

        return $warnings;
    }
}
