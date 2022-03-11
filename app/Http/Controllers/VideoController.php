<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\CoursesModules;
use App\Models\CoursesUsers;
use App\Models\ExercisesAlternatives;
use App\Models\ModulesVideos;
use App\Models\User;
use App\Models\VideosComments;
use App\Models\VideosExercises;
use App\Models\VideosNotes;
use App\Models\VideosWatcheds;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
    private function getClientLooged()
    {
        return Auth::user();
    }

    public function setNewCourseModule(Request $request, Int $id_course) 
    {
        //Check if the course is user
        $check_course = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $id_course)->first();

        if(!$check_course) return ['error' => 'Este curso não é seu'];

        $name = $request->input('name');

        if($name) {
            $save = new CoursesModules();
            $save->name = $name;
            $save->id_course = $id_course;
            $save->save();

        } else {
            return ['error' => 'Envie o nome para o modulo'];
        }

        return ['error' => ''];
    }

    public function setEditCourseModule(Request $request, Int $id_module)
    {
        $query_module = CoursesModules::find($id_module);

        if(!$query_module) return ['error' => 'Este módulo não existe'];

        //Check if the course is user
        $check_course = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $query_module->id_course)->first();

        if(!$check_course) return ['error' => 'Este curso não é seu'];

        //Edit
        $name = $request->input('name');

        if($name) {
            CoursesModules::where('id', $id_module)->update([
                'name' => $name
            ]);
        } else {
            return ['error' => 'Envie um novo nome para o módulo'];
        }

        return ['error' => ''];
    }

    public function setNewModuleVideo(Request $request, Int $id_module)
    {
        $array_return = ['error' => ''];

        $query_module = CoursesModules::find($id_module);

        if(!$query_module) return ['error' => 'Este módulo não existe'];

        //Check if the course is user
        $check_course = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $query_module->id_course)->first();

        if(!$check_course) return ['error' => 'Este curso não é seu'];

        //Data of request
        $title = $request->input('title');
        
        if(!$title) return ['error' => 'envie um titulo para o vídeo'];

        //Save video
        if(isset($_FILES['file'])) {
            $arquivo = $_FILES['file'];
            $extension = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            $name_file = $this->getClientLooged()->id.'@'.rand(615, 5173179).rand(1, 5342).rand(19, 173528).rand(182, 525382).time().rand(10, 3292323); 

            if($extension != 'mp4') return ['error' => 'O arquivo deve ter o formato mp4'];

            move_uploaded_file($arquivo['tmp_name'], 'videos/'.$name_file.'.'.$extension);
        } else {
            return ['error' => 'Envie uma imagem'];
        }

        $save = new ModulesVideos();
        $save->id_module = $id_module;
        $save->id_course = $$query_module->id_course;
        $save->title = $title;
        $save->src = asset('videos'.'/'.$name_file.'.'.$extension);
        $save->save();

        return $array_return;
    }

    public function setDelModuleVideo(Int $id_video) {
        $query_video = ModulesVideos::find($id_video);

        //Se não existir o vído...
        if(!$query_video) return ['error' => 'Vídeo não encontrado - 404'];

        $query_module = CoursesModules::select('id_course')->find($query_video->id_module);

        //Se não existir o vído...
        if(!$query_module) return ['error' => 'Permissão negada'];

        //Check if the course is user
        $check_course = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $query_module->id_course)->first();

        if(!$check_course) return ['error' => 'Este curso não é seu'];

        //Delete file
        $file_path = str_replace(env('APP_URL'), '', $query_video->src);

        unlink($file_path);

        $query_video->delete();

        return ['error' => ''];
    }

    public function setEditModuleVideo(Request $request, Int $id_video)
    {
        $query_video = ModulesVideos::find($id_video);

        //Se não existir o vído...
        if(!$query_video) return ['error' => 'Vídeo não encontrado - 404'];

        $query_module = CoursesModules::select('id_course')->find($query_video->id_module);

        //Se não existir o vídeo...
        if(!$query_module) return ['error' => 'Permissão negada'];

        //Check if the course is user
        $check_course = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $query_module->id_course)->first();

        if(!$check_course) return ['error' => 'Este curso não é seu'];

        //Edit
        $title = $request->input('title');

        if($title) {
            ModulesVideos::where('id', $id_video)->update([
                'title' => $title
            ]);
        } else {
            return ['error' => 'Envie um novo titulo para o vídeo'];
        }

        return ['error' => ''];
    }

    public function setNewVideoComment(Request $request, Int $id_video)
    { 
        $query_video = ModulesVideos::find($id_video);

        //Se não existir o vídeo...
        if(!$query_video) return ['error' => 'Vídeo não encontrado - 404'];

        //Save
        $body = $request->input('body'); 

        if(!($body))  return ['error' => 'Envie o seu comentário'];

        //Save
        $save = new VideosComments();
        $save->id_video = $id_video;
        $save->id_user = $this->getClientLooged()->id;
        $save->body = $body; 
        $save->save();
         

        return ['error' => ''];
    }

    public function setNewVideoNote(Request $request, Int $id_video) 
    {
        $query_video = ModulesVideos::find($id_video);

        //Se não existir o vídeo...
        if(!$query_video) return ['error' => 'Vídeo não encontrado - 404'];
        
        $check_note = VideosNotes::where('id_video', $id_video)->where('id_user', $this->getClientLooged()->id)->first();

        //Request data
        $body = $request->input('body');

        if(!($body)) return ['error' => 'Envie campo body'];

        if($check_note) {
            //Edit
            VideosNotes::where('id', $check_note->id)->update([
                'body' => $body
            ]);
        } else {
            //Save
            $save = new VideosNotes();
            $save->id_video = $id_video;
            $save->id_user = $this->getClientLooged()->id;
            $save->body = $body;
            $save->save();
        }

        return ['error' => ''];
    }

    public function setNewVideoWatched(Request $request, Int $id_video)
    {
        $query_video = ModulesVideos::find($id_video);

        //Se não existir o vídeo...
        if(!$query_video) return ['error' => 'Vídeo não encontrado - 404']; 
        
        //Mode
        $done = $request->input('done');

        if($done == null) return ['error'=>'Envie o tipo de açao'];

        if($done == 'del') {
            //Delete
            VideosWatcheds::where('id_video', $id_video)
            ->where('id_user', $this->getClientLooged()->id)
            ->delete();
        } else if($done == 'save') { 
            $q = VideosWatcheds::where('id_video', $id_video)
            ->where('id_user', $this->getClientLooged()->id)
            ->first();
           
            if(!$q) {
                 //Save
                $save = new VideosWatcheds();
                $save->id_video = $id_video;
                $save->id_user = $this->getClientLooged()->id; 
                $save->save();
            }
        }

        return ['error' => ''];
    }

    public function getVideoUsingId(Int $id_video) 
    {
        $video = ModulesVideos::find($id_video);

        if(!$video) return ['error' => '404'];

        $next_video = ModulesVideos::where('id_course', $video->id_course)->find($id_video + 1);
        $prev_video = ModulesVideos::where('id_course', $video->id_course)->find($id_video - 1);
        $video_watched = VideosWatcheds::where('id_user', $this->getClientLooged()->id)->where('id_video', $video->id)->count();

        if($video_watched > 0) {
            $video_watched = true;
        } else {
            $video_watched = false;
        }

        $data = [
            'video' => $video,
            'next_video' => $next_video,
            'prev_video' => $prev_video,
            'video_watched' => $video_watched
        ];
 
        return $data;
    }

    public function getVideoWatched($id_video)
    { 
        $video_watched = VideosWatcheds::where('id_user', $this->getClientLooged()->id)->where('id_video', $id_video)->count();

        if($video_watched > 0) {
            $video_watched = true;
        } else {
            $video_watched = false;
        }

        return ['info' => $video_watched];
    }

    public function getVideoNote($id_video)
    {
        $note = VideosNotes::where('id_user', $this->getClientLooged()->id)->where('id_video', $id_video)->first();
        if($note) {
            return ['text' =>  $note->body];
        } else {
            return ['text' => ''];
        }
    }

    public function getVideoComments($id_video) 
    {
        $query = DB::select("SELECT vc.*, u.name FROM videos_comments as vc LEFT JOIN users as u on(vc.id_user = u.id) WHERE vc.id_video = :id ORDER BY vc.date DESC", [
            'id' => $id_video
        ]);

        foreach($query as $k => $v) { 
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

            $query[$k]->date = $date;
        }

        return $query;
    }

    public function getVideoCommentUsingId($id_comment)
    {
        $comment = VideosComments::find($id_comment);
 
        if($comment) {
            $user = User::select('name')->find($comment->id_user);
            $comment->name = $user->name;

            return $comment;
        } else {
            return ['404'];
        } 
    }

    public function getVideoExercises(Int $id_video)
    {   
        $query_video = ModulesVideos::find($id_video);

        //Se não existir o vídeo...
        if(!$query_video) return ['error' => 'Vídeo não encontrado - 404'];

        $query_module = CoursesModules::select('id_course')->find($query_video->id_module);

        //Se não existir o modulo...
        if(!$query_module) return ['error' => 'Permissão negada'];

        //Check if the course is user
        $check_course = CoursesUsers::where('id_user', $this->getClientLooged()->id)->where('id_course', $query_module->id_course)->first();

        if(!$check_course) return ['error' => 'Este curso não é seu'];

        $exercises = VideosExercises::select('id', 'title')->where('id_video', $id_video)->get();

        //Data
        $data = [
            'exercises' => $exercises,
            'count_exercises' => count($exercises)
        ];

        foreach ($exercises as $key => $value) {
            $data['exercises'][$key] = $value;
            $alternatives = ExercisesAlternatives::select('id', 'title')->where('id_exercise', $value->id)->orderBy('order', 'ASC')->get();

            $data['exercises'][$key]['alternatives']=  $alternatives;
        }

        return $data;
    }

    public function getCheckExerciseCorrect(Int $id_exercise)
    {
        $exercise = ExercisesAlternatives::find($id_exercise);

        if(!$exercise) return ['error' => '404'];

        if($exercise->correct == 1) {
            return ['correct' => true];
        } else {
            return ['correct' => false];
        }
    }
} 
