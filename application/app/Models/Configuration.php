<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    public function getViewValueAttribute()
    {
        if(strtoupper($this->type) == "MULTIPLE")
        {
        	switch (strtoupper($this->selection)) {
        		case 'POSITION':
        			$index = Position::all();
        			$list_value = explode(', ', $this->value);
        			$view = '';

                    if($this->value != null)
                    {
                        foreach ($list_value as $list) {
                            $view .= '<span class="label label-default">' . (Position::find($list)->name ?? '?'). '</span> ';
                        }
                    }
        			

        			return $view;
        			break;

        		case 'DIVISION':
        			$index = Division::all();
        			$list_value = explode(', ', $this->value);
        			$view = '';

                    if($this->value != null)
                    {
                        foreach ($list_value as $list) {
                            $view .= '<span class="label label-default">' . (Division::find($list)->name ?? '?') . '</span> ';
                        }
                    }
            			

        			return $view;
        			break;

        		case 'USER':
        			$index = \App\User::all();
        			$list_value = explode(', ', $this->value);
        			$view = '';

                    if($this->value != null)
                    {
                        foreach ($list_value as $list) {
                            $view .= '<span class="label label-default">' . (\App\User::find($list)->fullname ?? '?') . '</span> ';
                        }
                    }
            			
        			return $view;
        			break;
        		
        		default:
        			# code...
        			break;
        	}
        }
        else if(strtoupper($this->type) == "SELECTION")
        {
            switch (strtoupper($this->selection)) {
                case 'POSITION':
                    return Position::find($this->value)->name;
                    break;

                case 'DIVISION':
                    return Division::find($this->value)->name;
                    break;

                case 'USER':
                    return \App\User::find($this->value)->fullname;
                    break;
                
                default:
                    # code...
                    break;
            }
        }
        else
        {
        	return $this->value;
        }
    }
}
