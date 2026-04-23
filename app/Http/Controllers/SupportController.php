<?php

namespace App\Http\Controllers;

use App\Helpers\SettingsHelper;
use App\Jobs\Mail\SendTechnicalSupportEmailNotificationJob;
use App\Models\Support;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Faker\Factory as Faker;


class SupportController extends Controller
{

    // لايمكن الدخول اليها من الرابط الى الدعم الفني فقط   
    // public function __construct()
    // {
    //     // Protect routes with middleware
    //     $this->middleware(function ($request, $next) {
    //         if (!auth()->user() || !(auth()->user()->hasRole('Technecal_Support'))) {
    //             abort(403, 'Access denied');
    //         }

    //         return $next($request);
    //     });
    // }


    // توليد رقم عشوائي للتذكرة
    public function generateUniqueTicketNumber()
    {
        $faker = Faker::create();

        do {
            // توليد رقم عقد فريد بالتنسيق C-####-####
            $TicketNumber = $faker->unique()->numerify('T-####-####');
        } while (Support::where('ticket_number', $TicketNumber)->exists());

        return $TicketNumber;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $supports = Support::all();
                // $supports = Support::with('user')->orderBy('created_at', 'desc');



                return DataTables::of($supports)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($row) {
                        //  \Carbon\Carbon::parse($row->created_at)->locale('ar')->translatedFormat('d F Y');
                        return \Carbon\Carbon::parse($row->created_at)->diffForHumans();
                    })
                    ->addColumn('ststus', function ($row) {
                        // هنا، نعيد أسماء الأدوار كمصفوفة
                        return $row->ststus ? $row->ststus : 'غير متوفر';
                    })
                    ->addColumn('user_id', function ($row) {
                        // هنا، نعيد أسماء الأدوار كمصفوفة
                        return $row->user ? $row->user->name : 'غير متوفر';
                    })
                    ->addColumn('attachment', function ($row) {
                        if ($row->attachment) {
                            // توليد رابط المرفق باستخدام Storage::url
                            $url = Storage::url($row->attachment);
                            // إنشاء زر لعرض المرفق في تبويبة جديدة
                            return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-primary">عرض المرفق</a>';
                        } else {
                            return 'غير متوفر';
                        }
                    })
                    ->addColumn('details', function ($row) {

                        return '<a href="' . route('supports.show', $row->id) . '"  class="btn btn-sm btn-info view-note-btn" >عرض التذكرة</a>';
                    })

                    ->addColumn('action', function ($row) {
                        $editUrl = route('supports.edit', $row->id);
                        $deleteUrl = route('supports.destroy', $row->id);
                        $formId = 'delete-form-' . $row->id;
                        $csrfField = csrf_field();
                        $methodField = method_field('DELETE');

                        // أزرار التحرير والحذف مع التحقق من الصلاحيات
                        $editButton = '';
                        $deleteButton = '';

                        // تحقق من صلاحية التعديل

                        // $editButton = '<a href="' . $editUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-edit"></i></a>';

                        // تحقق من صلاحية الحذف
                        $deleteButton = '<a href="javascript:void(0);" onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm text-secondary">
                                                <i class="ti ti-trash"></i>
                                             </a>
                                             <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" style="display: none;">
                                                ' . $csrfField . '
                                                ' . $methodField . '
                                             </form>';
                        // زر الرد
                        $replyButton = '';
                        $viewReplyButton = '';

                        // إذا كان هناك رد، عرض زر "عرض الرد" فقط
                        // $viewReplyButton = '<a href="javascript:void(0);" onclick="viewReply(' . $row->id . ')" class="btn btn-sm text-secondary " id="view-reply-button-' . $row->id . '" >
                        //                                 <i class="ti ti-eye"></i>
                        //                             </a>';

                        // // إذا لم يكن هناك رد، عرض زر "الرد" فقط
                        // $replyButton = '<a href="javascript:void(0);" onclick="openReplyModal(' . $row->id . ')" class="btn btn-sm text-secondary " id="reply-button-' . $row->id . '" >
                        //                         <i class="ti ti-message-circle"></i>
                        //                     </a>';


                        return '<span class="d-inline-flex align-items-center">' . $viewReplyButton . '' . $replyButton . ' ' . $deleteButton . '</span>';
                    })

                    ->rawColumns(['action', 'attachment', 'details'])
                    ->toJson();
            }

            // جلب جميع الأدوار المتاحة


            return view('supports.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageConfigs = ['myLayout' => 'front'];
        return view('supports.create', ['pageConfigs' => $pageConfigs]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // تعريف قواعد التحقق من صحة البيانات
        $rules = [
            'ticket_classification' => 'required|in:اقتراح,شكوى,تعديلات برمجية',
            'title' => 'required|string|max:255',
            'priority' => 'required|in:عاجلة,عالية,متوسطة,منخفضة',
            'notes' => 'required|nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ];

        $messages = [
            'ticket_classification.required' => 'حقل تصنيف التذكرة مطلوب.',
            'ticket_classification.in' => 'القيمة المدخلة في تصنيف التذكرة غير صالحة.',

            'title.required' => 'حقل عنوان الرسالة مطلوب.',
            'title.string' => 'حقل عنوان الرسالة يجب أن يكون نصاً.',
            'title.max' => 'حقل عنوان الرسالة لا يجب أن يتجاوز 255 حرفاً.',

            'priority.required' => 'حقل الأولوية مطلوب.',
            'priority.in' => 'القيمة المدخلة في الأولوية غير صالحة.',

            'notes.required' => ' حقل الوصف مطلوب    .',
            'notes.string' => 'حقل الوصف يجب أن يكون نصاً.',

            'attachment.file' => 'المرفق يجب أن يكون ملفاً.',
            'attachment.mimes' => 'نوع الملف المرفق غير مدعوم. يجب أن يكون من أنواع: jpg، jpeg، png، pdf، doc، docx.',
            'attachment.max' => 'حجم الملف المرفق لا يجب أن يتجاوز 2 ميجابايت.',


        ];

        // التحقق من صحة الطلب بناءً على القواعد المحددة
        $validatedData = $request->validate($rules, $messages);

        // إنشاء سجل اتصال جديد
        $support = new Support();

        // تعيين user_id إذا كان المستخدم مسجل الدخول، وإلا اجعله null
        $support->user_id = auth()->check() ? auth()->id() : null;

        // تعيين الحقول الأخرى
        $support->ticket_classification = $validatedData['ticket_classification'];
        $support->title = $validatedData['title'];
        $support->priority = $validatedData['priority'];
        $support->notes = $validatedData['notes'];
        $support->ticket_number = $this->generateUniqueTicketNumber();


        // التعامل مع المرفقات
        if ($request->hasFile('attachment')) {
            // تخزين الملف في مجلد 'attachments' داخل 'storage/app/public'
            $support->attachment = $request->file('attachment')->store('attachments', 'public');
        }

        // حفظ السجل في قاعدة البيانات
        $support->save();

        // توليد ملف PDF للتذكرة وتخزينه
        $pdfPath = $this->generateSupportTicketPDF($support->id);

        // تجهيز بيانات التذكرة، مع تضمين رابط ملف ال-PDF المُولَّد
        $ticketData = [
            'user_name'             => $support->user->name,
            'subject'               => $support->title,
            'pdf_attachment'        => asset($pdfPath),
            'office_name'           => SettingsHelper::get('office_name'),
        ];

        // إرسال بريد لصاحب التذكرة مع إرفاق ملف PDF
        SendTechnicalSupportEmailNotificationJob::dispatch([
            'email' => $support->user->email,
        ], $ticketData, 'user');

        // إرسال إشعار للبريد الخاص بالدعم الفني (مصفوفة عناوين)
        $technicalEmails = SettingsHelper::get('support_emails');
        if ($technicalEmails) {
            foreach ($technicalEmails as $techEmail) {
                SendTechnicalSupportEmailNotificationJob::dispatch([
                    'email' => $techEmail,
                ], $ticketData, 'technical');
            }
        }

        // إعادة التوجيه مع رسالة نجاح
        return redirect()->route('userSuppports')->with('success', 'تم إرسال رسالتك بنجاح!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Support $support)
    {
        return view('supports.admin_support_show', compact('support'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Support $support)
    {
        $pageConfigs = ['myLayout' => 'front'];
        return view('supports.edit', ['pageConfigs' => $pageConfigs, 'support' => $support]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Support $support)
    {
        $support = Support::findOrFail($support->id);

        // تعريف قواعد التحقق من البيانات
        $rules = [
            'ticket_classification' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'priority' => 'required|string|max:50',
            'notes' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // تعديل أنواع الملفات والحجم حسب الحاجة
            'delete_attachment' => 'nullable|boolean',
        ];
        $messages = [
            'ticket_classification.required' => 'حقل تصنيف التذكرة مطلوب.',
            'ticket_classification.in' => 'القيمة المدخلة في تصنيف التذكرة غير صالحة.',

            'title.required' => 'حقل عنوان الرسالة مطلوب.',
            'title.string' => 'حقل عنوان الرسالة يجب أن يكون نصاً.',
            'title.max' => 'حقل عنوان الرسالة لا يجب أن يتجاوز 255 حرفاً.',

            'priority.required' => 'حقل الأولوية مطلوب.',
            'priority.in' => 'القيمة المدخلة في الأولوية غير صالحة.',

            'notes.required' => ' حقل الوصف مطلوب    .',
            'notes.string' => 'حقل الوصف يجب أن يكون نصاً.',

            'attachment.file' => 'المرفق يجب أن يكون ملفاً.',
            'attachment.mimes' => 'نوع الملف المرفق غير مدعوم. يجب أن يكون من أنواع: jpg، jpeg، png، pdf، doc، docx.',
            'attachment.max' => 'حجم الملف المرفق لا يجب أن يتجاوز 2 ميجابايت.',


        ];

        $validatedData = $request->validate($rules, $messages);

        $support->ticket_classification = $validatedData['ticket_classification'];
        $support->title = $validatedData['title'];
        $support->priority = $validatedData['priority'];
        $support->notes = $validatedData['notes'];

        // التعامل مع حذف المرفق الحالي
        if ($request->has('delete_attachment') && $validatedData['delete_attachment']) {
            if ($support->attachment && Storage::disk('public')->exists($support->attachment)) {
                Storage::disk('public')->delete($support->attachment);
            }
            $support->attachment = null;
        }

        // التعامل مع تحميل مرفق جديد
        if ($request->hasFile('attachment')) {
            // حذف المرفق القديم إذا كان موجودًا
            if ($support->attachment && Storage::disk('public')->exists($support->attachment)) {
                Storage::disk('public')->delete($support->attachment);
            }

            // حفظ المرفق الجديد
            $path = $request->file('attachment')->store('attachments', 'public');
            $support->attachment = $path;
        }

        // حفظ التغييرات في قاعدة البيانات
        $support->save();

        return redirect()->route('userSuppports')->with('success', 'تم تحديث رسالتك بنجاح!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Support $support)
    {
        try {
            // إذا كان هناك مرفق، قم بحذفه من التخزين
            if ($support->attachment && Storage::disk('public')->exists($support->attachment)) {
                Storage::disk('public')->delete($support->attachment);
            }

            // حذف السجل من قاعدة البيانات
            $support->delete();

            // إعادة التوجيه مع رسالة نجاح
            return redirect()->back()->with('success', 'تم حذف التذكرة بنجاح!');
        } catch (\Exception $e) {
            // إعادة التوجيه مع رسالة خطأ في حالة حدوث مشكلة
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف التذكرة.');
        }
    }


    public function userSuppports(Request $request)
    {
        try {
            if ($request->ajax()) {
                $supports = Support::where('user_id', Auth::user()->id)->select([
                    'user_id',
                    'ticket_classification',
                    'title',
                    'ticket_number',
                    'priority',
                    'notes',
                    'attachment',
                    'reply',
                    'id',
                    'status',
                    'created_at'
                ]);



                return DataTables::of($supports)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($row) {
                        // return \Carbon\Carbon::parse($row->created_at)->locale('ar')->translatedFormat('d F Y');
                        return \Carbon\Carbon::parse($row->created_at)->diffForHumans();
                    })
                    ->addColumn('ststus', function ($row) {
                        // هنا، نعيد أسماء الأدوار كمصفوفة
                        return $row->ststus ? $row->ststus : 'غير متوفر';
                    })

                    ->addColumn('details', function ($row) {

                        return '<a href="' . route('supports.reply.show', $row->id) . '"  class="btn btn-sm btn-info view-note-btn" >عرض التذكرة</a>';
                    })

                    ->addColumn('action', function ($row) {
                        $editUrl = route('supports.edit', $row->id);
                        $deleteUrl = route('supports.destroy', $row->id);
                        $formId = 'delete-form-' . $row->id;
                        $csrfField = csrf_field();
                        $methodField = method_field('DELETE');

                        // أزرار التحرير والحذف مع التحقق من الصلاحيات
                        $viewReplyButton = '';
                        $editButton = '';
                        $deleteButton = '';

                        // تحقق من صلاحية التعديل

                        if ($row->status == 'جديد') {
                            $editButton = '<a href="' . $editUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-edit"></i></a>';
                            $deleteButton = '<a href="javascript:void(0);" onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm text-secondary">
                                            <i class="ti ti-trash"></i>
                                         </a>
                                        <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" style="display: none;">
                                            ' . $csrfField . '
                                            ' . $methodField . '
                                        </form>
                                        ';
                        } else {
                            $viewReplyButton = '<a href="' . route('supports.reply.show', $row->id) . '"  class="btn btn-sm btn-info view-note-btn" >عرض التفاصيل</a>';
                        }




                        return $viewReplyButton . '' . $editButton . ' ' . $deleteButton;
                    })

                    ->rawColumns(['action', 'details'])
                    ->toJson();
            }

            // جلب جميع الأدوار المتاحة


            return view('supports.user_support');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    public function reply(Request $request, string $id)
    {
        try {
            // تعريف قواعد التحقق من البيانات
            $rules = [
                'status' => 'required|in:جديد,انتظار رد العميل,تمت المعالجة',
            ];

            // اجعل حقل `reply` إجباريًا فقط إذا كانت الحالة "تمت المعالجة"
            if ($request->status === 'تمت المعالجة') {
                $rules['reply'] = 'required|string';
            }

            $validatedData = $request->validate($rules, [
                'reply.required' => 'حقل الرد مطلوب عند اختيار الحالة "تمت المعالجة".',
                'reply.string' => 'يجب أن يكون الرد نصيًا.',
                'status.required' => 'تحديث الحالة مطلوب.',
                'status.in' => 'الحالة غير صالحة.',
            ]);

            // العثور على التذكرة
            $support = Support::findOrFail($id); // استخدام $id لتحديد التذكرة المطلوبة

            // if ($request->status === 'تمت المعالجة') {
            $support->processed_by = Auth::user()->id;

            // }
            // تحديث التذكرة
            $support->reply = $request->reply;
            $support->status = $request->status;
            $support->reply_date = Carbon::now();
            $support->save();

            // توليد ملف PDF للتذكرة وتخزينه
            $pdfPath = $this->generateSupportTicketPDF($support->id);

            // تجهيز بيانات التذكرة، مع تضمين رابط ملف ال-PDF المُولَّد
            $ticketData = [
                'user_name'             => $support->user->name,
                'subject'               => $support->title,
                'pdf_attachment'        => asset($pdfPath),
                'office_name'           => SettingsHelper::get('office_name'),
            ];

            SendTechnicalSupportEmailNotificationJob::dispatch([
                'email' => $support->user->email,
            ], $ticketData, 'reply');

            $technicalEmails = SettingsHelper::get('support_emails');
            if ($technicalEmails) {
                foreach ($technicalEmails as $techEmail) {
                    SendTechnicalSupportEmailNotificationJob::dispatch([
                        'email' => $techEmail,
                    ], $ticketData, 'reply');
                }
            }

            // رسالة نجاح
            return redirect()->route('supports.show', $support->id)->with('success', 'تم الرد على التذكرة بنجاح!');
        } catch (\Exception $e) {
            // تسجيل الخطأ في ملف log

            // رسالة خطأ عند فشل العملية
            return redirect()->back()->with('error', 'حدث خطأ أثناء الرد. يرجى المحاولة لاحقاً.');
        }
    }

    // لعرض الرد
    public function getReply($id)
    {
        $support = Support::find($id);
        if ($support) {
            return response()->json(['reply' => $support->reply]);
        } else {
            return response()->json(['error' => 'الرد غير موجود'], 404);
        }
    }


    public function showTicket($id)
    {
        $support = Support::findOrFail($id);
        if ($support->user_id == Auth::user()->id)
            return view('supports.user_support_show', compact('support'));
        else
            abort(403);
    }


    //لاضافة ردود للتذكرة
    public function addReplyTechnecal(Request $request, string  $id)
    {
        try {
            // التحقق من صحة البيانات المدخلة
            $request->validate([
                'content' => 'required|string',
            ]);

            // جلب التذكرة حسب المعرف
            $support = Support::findOrFail($id);

            // إنشاء رد جديد مرتبط بالتذكرة
            $support->replies()->create([
                'content' => $request->content,
                'user_id' => auth()->id(), // المستخدم الحالي الذي قام بالرد
            ]);

            // إعادة التوجيه مع رسالة نجاح

            return redirect()->route('supports.show', $id)->with('success', 'تم إرسال الرد بنجاح.');
        } catch (\Exception $e) {
            // رسالة خطأ عند فشل العملية
            return redirect()->back()->with('error', 'حدث خطأ أثناء الرد. يرجى المحاولة لاحقاً.');
        }
    }


    public function addReply(Request $request, string  $id)
    {
        try {
            // التحقق من صحة البيانات المدخلة
            $request->validate([
                'content' => 'required|string',
            ]);

            // جلب التذكرة حسب المعرف
            $support = Support::findOrFail($id);

            // إنشاء رد جديد مرتبط بالتذكرة
            $support->replies()->create([
                'content' => $request->content,
                'user_id' => auth()->id(), // المستخدم الحالي الذي قام بالرد
            ]);

            // إعادة التوجيه مع رسالة نجاح

            return redirect()->route('supports.reply.show', $id)->with('success', 'تم إرسال الرد بنجاح.');
        } catch (\Exception $e) {
            // رسالة خطأ عند فشل العملية
            return redirect()->back()->with('error', 'حدث خطأ أثناء الرد. يرجى المحاولة لاحقاً.');
        }
    }

    //
    public function print($id)
    {
        $support = Support::findOrFail($id);

        if (
            auth()->user()->hasRole('Technecal_Support') ||  // التحقق من الدور
            $support->user_id == auth()->id()  // التحقق من الصلاحيات الإضافية
        ) {
            $pageConfigs = ['myLayout' => 'blank'];
            return view('supports._partials.print_ticket', ['pageConfigs' => $pageConfigs, 'support' => $support]);
        } else {
            abort(403, 'ليس لديك الصلاحيات للوصول إلى هذا السجل.');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | export Support Ticket To PDF
    |--------------------------------------------------------------------------
    */
    // public function exportSupportTicketToPDF($id)
    // {
    //     $settings = SettingsHelper::get('office_name');
    //     try {
    //         // الحصول على التذكرة من قاعدة البيانات
    //         $support = Support::findOrFail($id);

    //         // مسارات ترويسة وفوتر التذكرة (يمكن تعديلها حسب إعدادات النظام)
    //         $headerPath = public_path('etmam.jpg');
    //         $footerPath = public_path('etmam-footer.png');

    //         // إعداد تكوين mPDF بما يشمل الخطوط والهوامش
    //         $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    //         $fontDirs = $defaultConfig['fontDir'];
    //         $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    //         $fontData = $defaultFontConfig['fontdata'];

    //         $config = [
    //             'mode' => 'utf-8',
    //             'format' => 'A4',
    //             'default_font' => 'almarai',
    //             'margin_left' => 10,
    //             'margin_right' => 10,
    //             'margin_top' => 40,
    //             'margin_bottom' => 40,
    //             'margin_header' => 0,
    //             'margin_footer' => 10,
    //             'orientation' => 'P',
    //             'fontDir' => array_merge($fontDirs, [
    //                 public_path('fonts/Almarai'),
    //             ]),
    //             'fontdata' => array_merge($fontData, [
    //                 'almarai' => [
    //                     'R' => 'Almarai-Regular.ttf',
    //                     'B' => 'Almarai-Bold.ttf',
    //                     'L' => 'Almarai-Light.ttf',
    //                     'useOTL' => 0xFF,
    //                     'useKashida' => 75,
    //                 ]
    //             ]),
    //             'default_font_size' => 12,
    //             'tempDir' => storage_path('app/public/temp')
    //         ];

    //         $mpdf = new \Mpdf\Mpdf($config);
    //         $mpdf->SetDirectionality('rtl');

    //         // إعداد CSS عام يمكن تعديله أو إضافته حسب الحاجة
    //         $stylesheet = '
    //         body { 
    //             font-family: almarai;
    //             font-size: 12px;
    //             line-height: 1.5;
    //             direction: rtl;
    //         }
    //         .footer {
    //             text-align: center;
    //             font-size: 10px;
    //             color: #777;
    //         }
    //         .ticket-info {
    //             background: #f8f9fa;
    //             padding: 20px;
    //             margin-bottom: 25px;
    //         }
    //         .ticket-info table {
    //             border-collapse: collapse;
    //             width: 100%;
    //         }
    //         .ticket-info td {
    //             padding: 8px;
    //             vertical-align: middle;
    //         }
    //         .ticket-info strong {
    //             margin-left: 8px;
    //             display: inline-block;
    //         }
    //         .ticket-info strong:after {
    //             content: ":";
    //             margin-right: 5px;
    //         }
    //         .ticket-info span {
    //             display: inline-block;
    //         }
    //     ';
    //         $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

    //         // إعداد ترويسة PDF باستخدام Blade (يمكنك ضبطها كما تشاء)
    //         // في هذا المثال سنستخدم ترويسة بسيطة باستخدام جدول لضمان المحاذاة الصحيحة
    //         if (file_exists($headerPath)) {
    //             $headerContent = '

    //                 <table width="100%"  dir="">
    //                     <tr>
    //                         <td style="text-align: right;">
    //                             <img src="' . $headerPath . '" style="height: 120px;" />
    //                         </td>

    //                         <td style="width:40%"></td>

    //                         <td style="text-align: ;">
    //                             <div>
    //                                 <br/>
    //                                 <br/>
    //                                 <p><strong>تاريخ التذكرة:</strong> ' . \Carbon\Carbon::parse($support->created_at)->isoFormat('dddd, D MMMM YYYY, h:mm A') . '</p>
    //                                 <br/>
    //                                 <p><strong>تاريخ المعالجة:</strong> ' . ($support->reply_date ? \Carbon\Carbon::parse($support->reply_date)->isoFormat('dddd, D MMMM YYYY, h:mm A') : 'لم يتم المعالجة بعد') . '</p>
    //                                 <br/>
    //                                 <p style="text-align: right !important; margin-bottom: 20px;">
    //                                 <strong> نظام:</strong> ' . $settings . '</p>
    //                             </div>
    //                         </td>

    //                     </tr>
    //                 </table>
    //                     <div style="border-bottom: 0.2px solid #ccc;padding-top:5px" /></div>
    //                     <div style="border-bottom: 0.2px solid #ccc;padding-top:5px" /></div>
    //                     <div style="border-bottom: 0.2px solid #ccc;padding-top:5px" /></div>';
    //             $mpdf->SetHTMLHeader($headerContent);
    //         }


    //         // إعداد الفوتر مع عبارة الشكر وصورة الفوتر
    //         if (file_exists($footerPath)) {
    //             $footerContent = '
    //             <div style="text-align: center; font-size: 12px; color: #777; padding: 5px 0;">
    //                 <span>إتمام لتقنية نظم المعلومات</span>
    //                 <p>شكرًا جزيلاً لتواصلكم معنا وثقتكم في خدماتنا.</p>
    //                 <p>' . ($support->reply == '' ? 'نود أن نبلغكم بأننا قد استلمنا طلبكم، وسنعمل على مراجعته في أقرب وقت ممكن.' : 'نسعى دائمًا لتقديم الأفضل لكم، ونتطلع إلى خدمتكم مجددًا بكل سرور.') . '</p>
    //             </div>
    //             <div style="border-bottom: 0.2px solid #ccc;padding-top:5px" /></div>
    //             <div style="border-bottom: 0.2px solid #ccc;padding-top:5px" /></div>
    //             <div style="border-bottom: 0.2px solid #ccc;padding-top:5px" /></div>
    //             <div class="footer" style="text-align: center; margin-top:5px;padding-top:5px">
    //                 <img src="' . $footerPath . '" style="height: 60px; max-width: 100%;" />
    //             </div>';
    //             $mpdf->SetHTMLFooter($footerContent);
    //         }

    //         // استخدام عرض Blade لتوليد محتوى PDF
    //         $html = view('supports.pdf', compact('support'))->render();

    //         // كتابة المحتوى إلى mPDF
    //         $mpdf->WriteHTML($html);

    //         // إعادة الملف كتنزيل
    //         return response()->streamDownload(function () use ($mpdf) {
    //             echo $mpdf->Output('', 'I');
    //         }, 'تذكرة_' . $support->ticket_number . '.pdf', ['Content-Type' => 'application/pdf']);
    //     } catch (\Exception $e) {
    //         \Log::error('Error generating Support Ticket PDF: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير التذكرة. يرجى المحاولة لاحقاً.');
    //     }
    // }


    public function generateSupportTicketPDF($id)
    {
        $settings = SettingsHelper::get('office_name');
        try {
            $support = Support::findOrFail($id);
            $headerPath = public_path('etmam.jpg');
            $footerPath = public_path('etmam-footer.png');

            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $config = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'almarai',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 40,
                'margin_bottom' => 40,
                'margin_header' => 0,
                'margin_footer' => 10,
                'orientation' => 'P',
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts/Almarai'),
                ]),
                'fontdata' => array_merge($fontData, [
                    'almarai' => [
                        'R' => 'Almarai-Regular.ttf',
                        'B' => 'Almarai-Bold.ttf',
                        'L' => 'Almarai-Light.ttf',
                        'useOTL' => 0xFF,
                        'useKashida' => 75,
                    ]
                ]),
                'default_font_size' => 12,
                'tempDir' => storage_path('app/public/temp')
            ];

            $mpdf = new \Mpdf\Mpdf($config);
            $mpdf->SetDirectionality('rtl');

            $stylesheet = '
            body { 
                font-family: almarai;
                font-size: 12px;
                line-height: 1.5;
                direction: rtl;
            }
            /* باقي CSS كما هو مطلوب */
        ';
            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

            if (file_exists($headerPath)) {
                $headerContent = '
                <table width="100%">
                    <tr>
                        <td style="text-align: right;">
                            <img src="' . $headerPath . '" style="height: 120px;" />
                        </td>
                        <td style="width:40%"></td>
                        <td style="text-align:;">
                            <div>
                                <p><strong>تاريخ التذكرة:</strong> ' . \Carbon\Carbon::parse($support->created_at)->isoFormat('dddd, D MMMM YYYY, h:mm A') . '</p>
                                <p><strong>تاريخ المعالجة:</strong> ' . ($support->reply_date ? \Carbon\Carbon::parse($support->reply_date)->isoFormat('dddd, D MMMM YYYY, h:mm A') : 'لم يتم المعالجة بعد') . '</p>
                                <p><strong>نظام:</strong> ' . $settings . '</p>
                            </div>
                        </td>
                    </tr>
                </table>
                <div style="border-bottom: 0.2px solid #ccc;"></div>';
                $mpdf->SetHTMLHeader($headerContent);
            }

            if (file_exists($footerPath)) {
                $footerContent = '
                <div style="text-align: center; font-size: 12px; color: #777;">
                    <span>إتمام لتقنية نظم المعلومات</span>
                    <p>شكرًا لتواصلك معنا.</p>
                </div>
                <div class="footer" style="text-align: center;">
                    <img src="' . $footerPath . '" style="height: 60px; max-width: 100%;" />
                </div>';
                $mpdf->SetHTMLFooter($footerContent);
            }

            $html = view('supports.pdf', compact('support'))->render();
            $mpdf->WriteHTML($html);

            // تحديد مسار تخزين الملف (يمكنك تعديل المسار حسب إعداداتك)
            $pdfFileName = 'تذكرة_' . $support->ticket_number . '.pdf';
            $pdfPath = storage_path('app/public/support_tickets/' . $pdfFileName);
            $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

            return 'storage/support_tickets/' . $pdfFileName;
        } catch (\Exception $e) {
            \Log::error('Error generating Support Ticket PDF: ' . $e->getMessage());
            throw $e;
        }
    }
}
