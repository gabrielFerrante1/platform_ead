<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\CoursesModules;
use App\Models\ExercisesAlternatives;
use App\Models\ModulesVideos;
use App\Models\VideosExercises;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciceController extends Controller
{   
    private function getClientLooged()
    {
        return Auth::user();
    }

    public function setNewExerciceInVideo(Request $request, Int $id_video)
    {
        $array_return = ['error' => ''];

        $query_video = ModulesVideos::find($id_video);

        //Se não existir o vídeo...
        if(!$query_video) return ['error' => 'Vídeo não encontrado - 404'];

        $query_module = CoursesModules::select('id_course')->find($query_video->id_module);

        //Se não existir o modulo...
        if(!$query_module) return ['error' => 'Permissão negada'];

        //Check if the course is user
        $check_course = Courses::where('id_user', $this->getClientLooged()->id)->where('id', $query_module->id_course)->first();

        if(!$check_course) return ['error' => 'Este curso não é seu'];

        //Create exercise
        $title = $request->input('title');

        if(!$title) { $array_return['error'] = 'Envie o titulo do exercisio'; return $array_return; }

        $save_exercice = new VideosExercises();
        $save_exercice->id_video = $id_video;
        $save_exercice->title = $title;
        $save_exercice->save();
         
        //Create alternatives
        $alternative_correct = $request->input('alternative_correct'); 
        
        $save_alternative = new ExercisesAlternatives();
        $save_alternative->id_exercise = $save_exercice->id; 
        $save_alternative->title = $alternative_correct;
        $save_alternative->correct = 1;
        $save_alternative->order = rand(20, 45);
        $save_alternative->save();

        for ($i=0; $i < 3; $i++) { 
            $save_alternatives = new ExercisesAlternatives();
            $save_alternatives->id_exercise = $save_exercice->id; 
            $save_alternatives->title = $request->input('alternative_incorrect_'.$i+1);
            $save_alternatives->correct = 0;
            $save_alternatives->order = rand(13, 43);
            $save_alternatives->save();
        }

        return $array_return;
    }
}
