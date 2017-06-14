<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{

    protected $fillable   = ['name', 'shortdesc', 'description', 'status', 'startdate', 'starttime', 'enddate', 'endtime', 'addedby', ' cover_image', 'type', 'price','expected_attendaees', 'venue','phone_number', 'email', 'latitude', 'longitude', 'entrypass', 'pagetype', 'url', 'html', 'meals', 'roles', 'updated_at', 'created_at'];

    protected $table      = 'events';
    protected $primaryKey = 'eventid';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @des RELATIONSHIP FROM EVENT TO SESSIONS WITH MANY
     */
    public function sessions()
    {
        return $this->hasMany('App\Modules\Events\Models\Sessions', 'event_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @des RELATIONSHIP FROM USER TO EVENT WITH MANY
     */
    public function event_userevent()
    {
        return $this->hasMany('App\Modules\Events\Models\Events', 'event_id');
    }

	 public function SaveImageActionAction($request)
    {
        $LoginId = \Auth::user()->UserID;
        $LoginName = trim(\Auth::user()->UserName);
        $TimeValue = \Carbon\Carbon::now();
        $CurrentDate = \Carbon\Carbon::parse($TimeValue)->format('Y-m-d H:i:s');

        $ActionData = array();
        $ActionData['ContactID']     = 0;//$request->Document_ContactID;
        $ActionData['InvoiceNumber'] = '';//trim($request->Document_InvoiceNumber);
        $ActionData['UserID']        = $LoginId;
        $ActionData['AddedBy']       = trim($LoginName);
        $ActionData['FileName']      = trim($request->Document_FileName);
        $ActionData['FileNote']      = trim($request->Document_FileNote);
        $ActionData['FileCreatedAt'] = $CurrentDate;

        /* Upload file code */
        $file = \Request::file('Document');
        $FileType = $file->getClientOriginalExtension();

        if (!CheckDocumentExt($FileType)) {
            $result = 0;
            $ActionData['message'] = \Lang::get('validate.validate_model_file_type_msg');
            return array($result, $ActionData);
        }

        $BucketName     = strtolower(\Auth::user()->HexiumID.'PORTYX');
        $document_path  =  'orphan_files';
        $result         = 0;
        $file_name      = time() . "-" . $file->getClientOriginalName();
        $final_filename = $document_path.'/'.$file_name;
        $aws_object     = new AwsActions();
        $s3_result      = $aws_object->Upload($BucketName,$file->getPathName(),$final_filename);

        if($s3_result['ObjectURL'] != '')
        {
            $new_s3_filepath = 'https://s3-eu-west-1.amazonaws.com/'.$BucketName.'/'.$final_filename;
            $ActionData['FilePath']  = $new_s3_filepath;
            $ActionData['FileS3Key'] = $file_name;
            $ActionData['FileType']  = $FileType;

            if($ActionData['FileName'] == '')
            {
                $ActionData['FileName'] = substr($file->getClientOriginalName(), 0,strpos($file->getClientOriginalName(), '.'.$FileType));
            }

            $result = DB::connection($this->MysqlSchemaName)->table($this->DocumentsTable)->insertGetId($ActionData);
        }
        return array($result, $ActionData);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @des RELATIONSHIP FROM POLL QUESTION TO SESSION WITH ONE
     */
    public function events_eventsticket()
    {
        return $this->hasOne('App\Modules\Events\Models\EventTickets','eventid','event_id');
    }
}
