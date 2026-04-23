<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTemplatesSeeder extends Seeder
{

    public function run(): void
    {
        DB::table('settings_templates')->insert([
            [
                'name' => 'نموذج عرض',
                'user_id' => 1,
                'template_type' => 'offers',
                'content' => '<p class="ql-align-right ql-direction-rtl">{{current_date}}</p><p><strong>الموضوع: {{offer_name}}</strong></p><p><strong>السادة/ {{customer_name}} سلمهم الله</strong></p><p><br></p><p>تحية طيبة:</p><p>بالإشارة إلى طلبكم التزويد بعرض سعر يتضمن أتعاب تنفيذ نطاق العمل الموضح أدناه، فإنه يسعدنا تقديم العرض الفني والمالي لكم وفقًا لما يلي:</p><p>{{technical_offer}}</p><p>{{financial_offer}}</p><p><strong>أحكام العرض</strong></p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>لا تشمل الأتعاب الواردة ضريبة القيمة المضافة أو الرسوم الحكومية ذات الصلة بأي من الخدمات أعلاه -إن وجدت-.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>هذا العرض يأخذ طابع السرية التامة ونشأ للغرض المشار إليه في البند (نطاق العمل) ولا يسمح بتمكين الغير من الاطلاع عليه أو نسخه أو تقديمه لأي طرف آخر مهما كانت الظروف والأسباب.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>لا يمثل هذا العرض إيجاباً ولا قبولاً بحد ذاته؛ وتتم المراسلات بين الطرفين فيما يتعلق بإبرام العقد وتنفيذ نطاق العمل المشمول بهذا العرض عن طريق الاجتماع المباشر أو عن طريق البريد الإلكتروني أو أي وسيلة أخرى يتم الاتفاق عليها.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>السرعة قيمة نتبناها، ويكون تنفيذ نطاق العمل خلال المدة القياسية من حيث الأصل اعتبارًا من تاريخ الاتفاق بين الطرفين؛ ويستثنى من هذه المدة أية ظروف طارئة أو خارجة عن إراداتنا؛ كالتأخر في التزويد بالمدخلات المطلوبة من العميل، أو مدة لدى الجهة الحكومية.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>يسري هذا العرض لمدة (7 أيام عمل من تاريخه).</li></ol><p><br></p><p class="ql-align-right ql-direction-rtl"><strong>مُنجِز</strong></p>',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'نموذج عقد',
                'user_id' => 1,
                'template_type' => 'contracts',
                'content' => '<p class="ql-align-center"><strong>عقد المحاماة</strong></p><p class="ql-align-justify">إنه في يوم ‏الأربعاء‏، بتاريخ {{current_date}} م [ويشار إليه فيما بعد بـ"<strong>تاريخ العقد</strong>"] بمدينة الرياض بالمملكة العربية السعودية تم الاتفاق بين كل من: </p><ol><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span><strong>مُنجِز</strong>، سجل تجاري رقم (1010451466)، وعنوانها 7013 الرياض 12341،</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span> البريد الإلكتروني (<a href="mailto:info@alburhan.sa" rel="noopener noreferrer" target="_blank">info@alburhan.sa</a>) [ويشار إليها فيما بعد بـ"<strong>الطرف الأول</strong>"].</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span><strong>وبين {{customer_name}}</strong> ، سجل مدني رقم ({{civil_registry}})، وعنوانه: {{address}} البريد الإلكتروني ({{email}})، جوال رقم ({{contact_number}}) [ويشار إليها فيما بعد بـ"<strong>الطرف الثاني</strong>"].</li></ol><p class="ql-align-justify">كما [يشار للطرف الأول والطرف الثاني معًا فيما بعد بـ"<strong>الطرفان</strong>" أو "<strong>الطرفين</strong>"].</p><p><strong style="background-color: white;">التمهيد</strong></p><p class="ql-align-justify">حيث إن الطرف الأول شركة مهنية مرخصة ومتخصصة في تقديم الخدمات القانونية، وتتوافر لديه الخبرات والمؤهلات والكوادر المؤهلة للقيام بالأعمال المتفق عليها في هذا العقد، وحيث إن الطرف الثاني يرغب من الطرف الأول القيام بتقديم الخدمات القانونية اللازمة لتولي الأعمال المشار إليها في البند الخاص بنطاق العمل ، وعليه فقد التقت إرادة الطرفين إيجابًا وقبولًا وهما بكامل أهليتهما القانونية والشرعية على ما يلي: </p><p><strong>إقرار التمهيد</strong></p><p class="ql-align-justify">يُعد التمهيد جزءا لا يتجزأ من هذا العقد، يُقرأ ويُفسر بها ومعها.</p><p><strong>نطاق العمل</strong></p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>دراسة المستندات والوثائق اللازمة.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>تولي كافة الإجراءات القانونية والشرعية لإثبات وصية المورث فيصل بن محمد الميموني صاحب الهوية رقم (1020416911) والتي كتبها -رحمة الله- بتاريخ (26/12/1445هـ) أمام المحكمة المختصة.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>إثبات استحقاق الطرف الثاني لنصيبه من الوصية، بصفته مستفيداً من الوصية،</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>إثبات استحقاق شوهاق حسين وشقيقه لنصيبه من الوصية، بصفته مستفيداً من الوصية.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>تولي كافة الإجراءات القانونية مع ورثة المورث بشأن الوصية ودياً.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>تولي كافة الإجراءات القانونية مع ورثة المورث بشأن الوصية في حال لم يتم إنهاء اثبات الوصية صلحاً.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>تولي كافة المرافعات القضائية وحضور الجلسات وكتابة الخطابات وكل ما يتطلب حضور ناظر الوقف.</li></ol><p>{{technical_offer}}</p><p>{{financial_offer}}</p><p><strong>التزامات الطرف الأول</strong></p><p class="ql-align-justify">يلتزم الطرف الأول بما يلي:</p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>بذل العناية القانونية اللازمة لتحقيق مصلحة الطرف الثاني المضمّنة في نطاق العمل، بما في ذلك القيام بكل ما يتطلبه تنفيذ هذا العقد من إرسال وكتابة الخطابات وحضور الجلسات وتقديم المذكرات والرد عليها، وقبول الأحكام ورفضها وإنهاء كافة الإجراءات التي تتطلب حضور الطرف الثاني وإنجاز العمل في الدعوى المحددة في نطاق العمل.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>الالتزام بقواعد وسلوكيات مهنة المحاماة في المملكة العربية السعودية بوجه عام وفيما يتعلق بتعارض المصالح بشكل خاص، وعدم تقديم الخدمات القانونية لطرف آخر يتعارض مع مصالح الطرف الثاني في أعمال هذ العقد قبل الحصول على موافقته، والتحلي بالمسؤولية المهنية والأخلاقية وببذل العناية والجهد الكافي والمعتبر عرفًا باستخدام قدراته وإمكاناته وخبراته لأغراض هذا العقد. </li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>عدم القيام بأي تسوية أو صلح في نطاق العمل، إلا بموافقة الطرف الثاني الكتابية.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>عدم القيام بالتنازل عن محل نطاق العمل، إلا بموافقة الطرف الثاني الكتابية.</li></ol><p><strong>التزامات الطرف الثاني</strong></p><p class="ql-align-justify">يلتزم الطرف الثاني بما يلي:</p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>تزويد الطرف الأول بالوكالات أو المستندات التي يطلبها لتنفيذ نطاق العمل، وعدم إلغاء الوكالة أو فسخ هذا العقد أو توكيل محامٍ آخر قبل انتهاء العقد، ويعد انتهاكًا لهذا الالتزام قيام الطرف الثاني بالفعل أو الامتناع بما يؤدي إلى عرقلة تنفيذ الطرف الأول لنطاق العمل.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>تزويد الطرف الأول بكافة المعلومات الصحيحة والمستندات والبيانات المتعلقة بنطاق العمل، وعدم إخفاء أي مستند أو معلومات من شأنها التأثير أو تغيير مسار الدعوى لصالح الخصم.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>عدم التنازلِ عن الدعاوى المقامة وفقًا لنطاق العمل، أو تركِ الخصومة من جانبه أو عن طريق الاتفاق مع الخصم، أو الصلحِ - سواء بمقابل أو دون مقابل-، أو تعمّدِ الطرف الثاني وقف الخصومة بأي شكل من الأشكال، دون إذن مكتوب من الطرف الأول. </li></ol><p><strong>إنهاء العقد</strong></p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>ينتهي العقد وفقًا لما هو محدد في مدة العقد.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>يحق للطرف الأول فسخ هذا العقد، مع استحقاقه لكامل الأتعاب المحددة في هذا العقد، وذلك في حال إخلال الطرف الثاني بأي من التزاماته الواردة في هذا العقد.</li></ol><p><strong>أحكام عامة</strong></p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>للطرف الأول الحق بالاستعانة بمن يشاء أو توكيل من يشاء لتنفيذ نطاق العمل في هذا العقد.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>يُعتد في تواريخ هذا العقد أو تقارير تنفيذه بالتقويم الميلادي؛ إلا فيما يخص مواعيد الجهات القضائية فتكون بالخيار.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>يُعتد في الأعمال والمراسلات والإشعارات بموجب هذا العقد باللغة العربية.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>تعد المستندات والمعلومات المقدمة من الطرف الثاني أساسًا في مصادر مدخلات تقديم الخدمة، وتقع على الطرف الثاني حصرًا المسؤولية في حال تزويده بأية معلومات، أو مستندات مضللة، أو غير صحيحة، أو مزورة، أو غير مكتملة.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>يقر الطرف الثاني بخصوص الأتعاب المقررة في هذا العقد بما يلي:</li><li data-list="ordered" class="ql-indent-1"><span class="ql-ui" contenteditable="false"></span>أن التسوية أو المصالحة أو الوصول إلى أي سند تنفيذي لتحقيق نطاق العمل أو قيام خصم الطرف الثاني بتحويل أي مستحقات له ضمن نطاق العمل -بعد إبرام هذا العقد-، يعد في حكم المحكوم به بشكل قطعي في الأتعاب.</li><li data-list="ordered" class="ql-indent-1"><span class="ql-ui" contenteditable="false"></span>أن قيمة مقدم الأتعاب ليست قابلة للاسترداد أو العدول، وهي مقابل رضائي نظير المجهود الذي يبذله الطرف الأول في دراسة الدعوى وإبداء الرأي الشرعي والقانوني تجاهها.</li><li data-list="ordered" class="ql-indent-1"><span class="ql-ui" contenteditable="false"></span>يحق للطرف الأول التوقف عن العمل بهذا العقد في حالة عدم دفع الطرف الثاني للأتعاب المحددة.</li><li data-list="ordered" class="ql-indent-1"><span class="ql-ui" contenteditable="false"></span>لا تشمل الأتعاب تكاليف أي قضية تتفرع عن الدعاوى الرئيسة في نطاق العمل، أو أية دعاوى تقام لدى أي جهة قضائية أو تنفيذية أخرى. </li><li data-list="ordered" class="ql-indent-1"><span class="ql-ui" contenteditable="false"></span>لا تشمل الأتعاب التكاليف القضائية، أو الرسوم الحكومية، أو مصاريف السفر، أو الاستعانة بالخبراء، أو أية أعمال لا يقوم بها مقدم الخدمات القانونية عرفًا.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>في حالة نشوء أي نزاع أو خلاف أو مطالبة تنشأ بموجب هذا العقد أو تتعلق به، فيجب السعي لأن تتم التسوية عن طريق الصلح، وفي حال عدم التسوية عن طريق الصلح خلال مدة أقصاها (10 أيام) من تاريخ جلسة الصلح؛ فإن التسوية تكون في المحكمة المختصة بمدينة الرياض.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>المعتمد في المراسلات والإشعارات بين الطرفين هو البريد الإلكتروني وواتساب رقم الجوال وفق ما هو محدد في هذا العقد، ويحق لأي طرف تعديل أي من الوسائل المذكورة بموجب إشعار يرسله للطرف الآخر؛ ويسري أثر هذا الإشعار بعد تقديمه وفق الوسائل المقررة في هذه الفقرة.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>لا يجوز التعديل أو التنازل عن أي بند من بنود هذا العقد إلا باتفاق ملحق وموقع من الطرفين.</li><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>حرر هذا العقد من نسختين ورقيتين، ووقع كل طرف على نسخة منها، بعد قراءتها وفهم محتواها للعمل بموجبها.</li></ol><p class="ql-direction-rtl ql-align-center"><br></p><p><strong>الطرف الاول                                                                                                                          الطرف الثاني</strong></p><p><strong>شركة نظام إتمام الخاص                                                                                                          {{customer_name}}</strong></p><p>ويمثلها في التوقيع على هذا العقد:       </p><p> المحامي/ <strong>حسين بن عبدالله الزهراني   </strong></p>',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'نموذج تعريف بالراتب',
                'user_id' => 1,
                'template_type' => 'salary_definition',
                'content' => '<h1 dir="RTL" style="text-align: left;"><span lang="AR-SA" style="font-size: 10pt;">{{current_date}}</span></h1>
<h1 dir="RTL" style="text-align: center;"><span style="font-size: 10pt;"><strong><span lang="AR-SA">تعريف بالراتب&nbsp;<span style="mso-spacerun: yes;">&nbsp;</span></span></strong></span></h1>
<p class="MsoNormal" dir="LTR" style="text-align: right; direction: ltr; unicode-bidi: embed;" align="right"><span style="font-size: 10pt;"><strong><span style="line-height: 107%;">&nbsp;</span></strong><span lang="AR-SA">تشهد مُنجِز بأن الموظف الموضح بياناته أدناه، أحد منسوبيها حتى تاريخه، وتم منحه هذه الشهادة بناء على طلبه، دون أدنى مسؤولية على الشركة في ذلك.</span></span></p>
<h2 dir="RTL" style="border: none; text-align: center;"><span style="font-size: 10pt;"><strong><span lang="AR-SA">بيانات الجهة الطالبة</span></strong></span></h2>
<div dir="rtl" align="center">
<table class="TableGrid1" dir="rtl" style="width: 100%; border-collapse: collapse; border: none; height: 124.8px; border-spacing: 0px;" border="1" width="95%" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border: 1pt solid gray; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 1.3;" align="center"><span lang="AR-SA" style="font-size: 10pt;">اسم الجهة</span></p>
</td>
<td style="width: 79.8%; border-top: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{recipient}}</span></strong></span></p>
</td>
</tr>
</tbody>
</table>
</div>
<h2 dir="RTL" style="border: none; text-align: center;"><span style="font-size: 10pt;"><strong><span lang="AR-SA">المعلومات الشخصية&nbsp;</span></strong></span></h2>
<div dir="rtl" align="center">
<table class="TableGrid1" dir="rtl" style="width: 100%; border-collapse: collapse; border: none; height: 124.8px; border-spacing: 0px;" border="1" width="95%" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border: 1pt solid gray; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 1.3;" align="center"><span lang="AR-SA" style="font-size: 10pt;">الاسم</span></p>
</td>
<td style="width: 79.8%; border-top: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{employee_name}}</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span lang="AR-SA" style="font-size: 10pt;">رقم الهوية</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">&nbsp;{{id_number}}</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span lang="AR-SA" style="font-size: 10pt;">الجنسية</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">&nbsp;{{nationality}}</span></strong></span></p>
</td>
</tr>
</tbody>
</table>
</div>
<h2 dir="RTL" style="border: none; text-align: center;"><span style="font-size: 10pt;"><strong><span lang="AR-SA">المعلومات الوظيفية </span></strong></span></h2>
<div dir="rtl" align="center">
<table class="TableGrid1" dir="rtl" style="width: 100%; border-collapse: collapse; border: none; height: 249.6px; border-spacing: 0px;" border="1" width="96%" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 41.6px;">
<td style="width: 20.2046%; border: 1pt solid gray; background: #f2f2f2; padding: 0.1in 5.4pt;" width="17%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span lang="AR-SA" style="font-size: 10pt;">الوظيفة</span></p>
</td>
<td style="width: 79.7415%; border-top: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-right: none; padding: 0.1in 5.4pt;" colspan="2" width="82%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{job_title}}&nbsp;</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2046%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" width="17%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span lang="AR-SA" style="font-size: 10pt;">تاريخ بداية العقد</span></p>
</td>
<td style="width: 79.7415%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" colspan="2" width="82%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR" lang="EN-GB">{{contract_start_date}}</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2046%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" rowspan="5" width="17%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span lang="AR-SA" style="font-size: 10pt;">الراتب</span></p>
</td>
<td style="width: 25.1438%; border-top: none; border-left: 1pt solid windowtext; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="27%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; line-height: normal;"><span lang="AR-SA" style="font-size: 10pt;">الراتب الأساسي</span></p>
</td>
<td style="width: 54.5977%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="54%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{basic_salary}}</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 25.1438%; border-top: none; border-left: 1pt solid windowtext; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="27%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; line-height: normal;"><span lang="AR-SA" style="font-size: 10pt;">بدل السكن</span></p>
</td>
<td style="width: 54.5977%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="54%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{housing_allowance}}</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 25.1438%; border-top: none; border-left: 1pt solid windowtext; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="27%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; line-height: normal;"><span lang="AR-SA" style="font-size: 10pt;">بدل النقل</span></p>
</td>
<td style="width: 54.5977%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="54%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{transportation_allowance}}</span></strong></span></p>
</td>
</tr>
<tr>
<td style="width: 25.1438%; border-top: none; border-left: 1pt solid windowtext; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; line-height: normal;"><span lang="AR-SA" style="font-size: 10pt;">بدلات أخرى</span></p>
</td>
<td style="width: 54.5977%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{other_allowances}}<br></span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 25.1438%; border-top: none; border-left: 1pt solid windowtext; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="27%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; line-height: normal;"><span lang="AR-SA" style="font-size: 10pt;">إجمالي الراتب</span></p>
</td>
<td style="width: 54.5977%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="54%">
<p class="MsoNormal" dir="LTR" style="margin-bottom: 0in; text-align: center; line-height: normal; direction: ltr; unicode-bidi: embed;" align="center"><span style="font-size: 10pt;"><strong>&nbsp;</strong><strong>{{total_salary}}&nbsp; <strong><span style="line-height: 107%;">S.R</span></strong></strong></span></p>
</td>
</tr>
</tbody>
</table>
</div>
<p class="MsoNormal" dir="RTL" style="tab-stops: 81.8pt;">&nbsp;</p>
<p class="MsoNormal" dir="RTL" style="tab-stops: 81.8pt;"><span style="font-size: 10pt;">الرئيس التنفيذي /&nbsp;</span></p>',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],


            [
                'name' => 'نموذج تثبيت راتب',
                'user_id' => 1,
                'template_type' => 'salary_fixation',
                'content' => '<h1 dir="RTL" style="text-align: left;"><span lang="AR-SA" style="font-size: 10pt;">{{current_date}}</span></h1>
<p class="MsoNormal" dir="RTL" style="text-align: center;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="line-height: 107%;">تثبيت راتب&nbsp;</span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="line-height: 115%;">السادة/ {{recipient}}</span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;"><span lang="AR-SA" style="font-size: 10pt; line-height: 115%;">السلام عليكم ورحمة الله وبركاته:</span></p>
<div dir="rtl" align="center">
<table class="TableGrid1" dir="rtl" style="width: 100%; border-collapse: collapse; border: none; height: 124.8px; border-spacing: 0px;" border="1" width="95%" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border: 1pt solid gray; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 1.3;" align="center"><span lang="AR-SA" style="font-size: 10pt;">الاسم</span></p>
</td>
<td style="width: 79.8%; border-top: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{employee_name}}</span></strong></span></p>
</td>
</tr>
<tr>
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">الجنسية</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{nationality}}&nbsp;</span></strong></span></p>
</td>
</tr>
<tr>
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">الهوية</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{id_number}}&nbsp;</span></strong></span></p>
</td>
</tr>
<tr>
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">الرقم الوظيفي</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{national_number}}&nbsp;</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">المسمى الوظيفي</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{job_title}}&nbsp;</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">تاريخ الالتحاق بالعمل</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">&nbsp;{{contract_start_date}}</span></strong></span></p>
</td>
</tr>
</tbody>
</table>
</div>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;"><span style="font-size: 10pt;"><span lang="AR-SA" style="line-height: 115%;">تقدم موظفنا الموضحة بياناته أعلاه طالباً تحويل راتبه وبدلاته الشهرية وجميع مستحقاته الوظيفية إلى حسابه الجاري</span></span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;"><span style="font-size: 10pt;"><span lang="AR-SA" style="line-height: 115%;">&nbsp;رقم ({{iban}}</span><span lang="AR-SA" style="line-height: 115%;">) لأجل سداد الديون التي سوف تترتب عليه لصالح {{recipient}}.</span></span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;"><span lang="AR-SA" style="font-size: 10pt; line-height: 115%;">لذا نود أن نؤكد لكم موافقتنا والتزامنا بهذا التحويل على الحساب البنكي الموضح أعلاه في المواعيد الشهرية، واستمرار ذلك حتى نهاية العلاقة الوظيفية معنا، مع التزامنا بعدم تغيير الحساب البنكي الموضح أعلاه إلا بعد حصوله على إخلاء طرف من قبلكم، ما دام المذكور يعمل لدينا.</span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;">&nbsp;</p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;"><span lang="AR-SA" style="font-size: 10pt; line-height: 115%;">ولكم جزيل الشكر..</span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;">&nbsp;</p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;">&nbsp;</p>
<div align="right">
<table class="MsoTableGrid" dir="rtl" style="border-collapse: collapse; border: none; width: 100%; border-spacing: 0px;" border="1" cellspacing="0" cellpadding="0">
<tbody>
<tr style="mso-yfti-irow: 0; mso-yfti-firstrow: yes; mso-yfti-lastrow: yes;">
<td style="width: 139.3pt; border: none; border-top: solid #7F7F7F 1.0pt; mso-border-top-themecolor: text1; mso-border-top-themetint: 128; mso-border-top-alt: solid #7F7F7F .5pt; padding: 0in 5.4pt 0in 5.4pt;" valign="top" width="186">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; line-height: 115%;"><span style="font-size: 10pt;"><strong><span lang="AR-SA">المدير الشريك/سعيد بن محمد القرني </span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 115%;" align="center"><span lang="AR-SA" style="font-size: 10pt; line-height: 115%;">&nbsp;</span></p>
</td>
<td style="width: 130.45pt; border: none; border-top: solid #7F7F7F 1.0pt; mso-border-top-themecolor: text1; mso-border-top-themetint: 128; mso-border-top-alt: solid #7F7F7F .5pt; padding: 0in 5.4pt 0in 5.4pt;" valign="top" width="174">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 115%;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA"><span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>التوقيع<span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span><span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong></span></p>
</td>
<td style="width: 143.05pt; border: none; border-top: solid #7F7F7F 1.0pt; mso-border-top-themecolor: text1; mso-border-top-themetint: 128; mso-border-top-alt: solid #7F7F7F .5pt; padding: 0in 5.4pt 0in 5.4pt;" valign="top" width="191">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 115%;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA"><span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>الختم</span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 115%;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA"><span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 115%;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">&nbsp;</span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 115%;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">&nbsp;</span></strong></span></p>
</td>
</tr>
</tbody>
</table>
</div>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;">&nbsp;</p>',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],


            [
                'name' => 'نموذج إفادة تدريب',
                'user_id' => 1,
                'template_type' => 'training_certificate',
                'content' => '<h1 dir="RTL" style="text-align: left; line-height: 1.4;"><span lang="AR-SA" style="font-size: 10pt;">{{current_date}}</span></h1>
<p class="MsoNormal" dir="RTL" style="text-align: right; line-height: 1.4;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">سعادة مدير {{recipient}}<span style="mso-tab-count: 1;">&nbsp;</span></span></strong><strong><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";"><span style="mso-tab-count: 2;">&nbsp;</span>سلمه الله</span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 1.4;">&nbsp;</p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 1.4;"><span lang="AR-SA" style="font-size: 10pt; line-height: 107%; font-family: "Sakkal Majalla";">السلام عليكم ورحمة الله وبركاته، وبعد:</span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 1.4;"><span style="font-size: 10pt;"><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">نفيدكم بأن المتدرب/ {{employee_name}}، سجل مدني رقم ({{id_number}}) يحمل شهادة التدريب رقم ({{training_number}})&nbsp;</span><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">، قد تدرب لدينا طوال فترة التدريب من تاريخ {{contract_start_date}}</span><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";"> وحتى تاريخ هذا اليوم {{current_date}}، وتميز بحسن السيرة والسلوك، وقد زاول خلال فترة التدريب القيام بما يلي:</span></span></p>
<ul>
<li class="MsoListParagraphCxSpFirst" dir="RTL" style="text-align: justify; text-indent: -0.25in;"><span style="font-size: 10pt;"><span style="line-height: 107%; font-family: Symbol;"><span style="mso-list: Ignore;"><span style="font-style: normal; font-variant: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-weight: normal; font-stretch: normal; line-height: normal; font-family: "Times New Roman";">&nbsp; &nbsp; &nbsp; &nbsp;</span></span></span><!--[endif]--><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">إعداد لوائح ومذكرات القضائية.</span></span></li>
<li class="MsoListParagraphCxSpMiddle" dir="RTL" style="text-align: justify; text-indent: -0.25in;"><span style="font-size: 10pt;"><span style="line-height: 107%; font-family: Symbol;"><span style="mso-list: Ignore;"><span style="font-style: normal; font-variant: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-weight: normal; font-stretch: normal; line-height: normal; font-family: "Times New Roman";">&nbsp; &nbsp; &nbsp; &nbsp;</span></span></span><!--[endif]--><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">تقييد دعاوى لدى المحاكم.</span></span></li>
<li class="MsoListParagraphCxSpMiddle" dir="RTL" style="text-align: justify; text-indent: -0.25in;"><span style="font-size: 10pt;"><span style="line-height: 107%; font-family: Symbol;"><span style="mso-list: Ignore;"><span style="font-style: normal; font-variant: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-weight: normal; font-stretch: normal; line-height: normal; font-family: "Times New Roman";">&nbsp; &nbsp; &nbsp; &nbsp;</span></span></span><!--[endif]--><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">المرافعة أمام المحاكم.</span></span></li>
<li class="MsoListParagraphCxSpMiddle" dir="RTL" style="text-align: justify; text-indent: -0.25in;"><span style="font-size: 10pt;"><span style="line-height: 107%; font-family: Symbol;"><span style="mso-list: Ignore;"><span style="font-style: normal; font-variant: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-weight: normal; font-stretch: normal; line-height: normal; font-family: "Times New Roman";">&nbsp; &nbsp; &nbsp; &nbsp;</span></span></span><!--[endif]--><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">إعداد تقارير عن القضايا المرفوعة، وإرسالها للعملاء.</span></span></li>
<li class="MsoListParagraphCxSpLast" dir="RTL" style="text-align: justify; text-indent: -0.25in;"><span style="font-size: 10pt;"><span style="line-height: 107%; font-family: Symbol;"><span style="mso-list: Ignore;"><span style="font-style: normal; font-variant: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-weight: normal; font-stretch: normal; line-height: normal; font-family: "Times New Roman";">&nbsp; &nbsp; &nbsp; &nbsp;</span></span></span><!--[endif]--><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">مراجعة دوائر قضائية وحكومية ذات صلة بالدعاوى المرفوعة.</span></span></li>
</ul>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 1.4;"><span lang="AR-SA" style="font-size: 10pt; line-height: 107%; font-family: "Sakkal Majalla";"><span style="mso-spacerun: yes;">&nbsp;</span>وبناءً على طلبه أعطي هذه الإفادة لتقديمها للإدارة العامة للمحاماة.</span></p>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 1.4;">&nbsp;</p>
<p class="MsoNormal" dir="RTL" style="line-height: 1.4; text-align: left;"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="line-height: 107%; font-family: "Sakkal Majalla";">المحامي/ حسين بن عبدالله الزهراني</span></strong></span></p>
<p class="MsoNormal" dir="RTL" style="line-height: 1.4; text-align: left;"><span lang="AR-SA" style="font-size: 10pt; line-height: 107%; font-family: "Sakkal Majalla";">ترخيص رقم (328/35)&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span></p>
<p class="MsoNormal" dir="RTL" style="line-height: 1.4; text-align: right;">&nbsp;</p>',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],


            [
                'name' => 'نموذج إخلاء طرف ',
                'user_id' => 1,
                'template_type' => 'clearance_certificates',
                'content' => '<h1 dir="RTL" style="text-align: left;"><span lang="AR-SA" style="font-size: 10pt;">{{current_date}}</span></h1>
<h1 dir="RTL" style="text-align: center;"><strong><span lang="AR-SA" style="font-size: 12pt;">إخلاء طرف</span></strong></h1>
<h2 dir="RTL" style="margin: 0in 0in 6pt; text-align: center;"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="font-family: "Sakkal Majalla";">المعلومات</span></strong></span></h2>
<div dir="rtl" align="center">
<table class="TableGrid1" dir="rtl" style="width: 100%; border-collapse: collapse; border: none; height: 124.8px; border-spacing: 0px; margin-left: auto; margin-right: auto;" border="1" width="95%" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border: 1pt solid gray; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: 1.3;" align="center"><span lang="AR-SA" style="font-size: 10pt;">اسم الموظف</span></p>
</td>
<td style="width: 79.8%; border-top: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span dir="LTR">{{employee_name}}</span></strong></span></p>
</td>
</tr>
<tr>
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">رقم الهوية</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{id_number}}&nbsp;</span></strong></span></p>
</td>
</tr>
<tr>
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">نوع التعاقد</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">عقد عمل</span></strong></span></p>
</td>
</tr>
<tr>
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">تاريخ بداية العقد</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{contract_start_date}}</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">تاريخ آخر يوم عمل</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">{{contract_end_date}}&nbsp;</span></strong></span></p>
</td>
</tr>
<tr style="height: 41.6px;">
<td style="width: 20.2%; border-right: 1pt solid gray; border-bottom: 1pt solid gray; border-left: 1pt solid gray; border-image: initial; border-top: none; background: #f2f2f2; padding: 0.1in 5.4pt;" width="20%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;">نوع الإخلاء</span></p>
</td>
<td style="width: 79.8%; border-top: none; border-left: 1pt solid gray; border-bottom: 1pt solid gray; border-right: none; padding: 0.1in 5.4pt;" width="79%">
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: center; line-height: normal;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA">انتهاء مدة العقد</span></strong></span></p>
</td>
</tr>
</tbody>
</table>
</div>
<h2 dir="RTL" style="margin: 0in 0in 6pt; text-align: center;">&nbsp;</h2>
<h2 dir="RTL" style="margin: 0in 0in 6pt; text-align: center;"><strong><span style="font-size: 10pt;"><span lang="AR-SA" style="font-family: "Sakkal Majalla";">الإقرار</span></span></strong></h2>
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: justify; line-height: 115%;"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="line-height: 115%; font-family: "Sakkal Majalla";">يقر الطرف الأول: ({{office_name}})،&nbsp;</span></strong><span lang="AR-SA" style="line-height: 115%; font-family: "Sakkal Majalla";">س.ت (رقم السجل التجاري )، أن الموظف الموضح بياناته أعلاه قد أُخلي طرفه من جميع العهد والالتزامات بكل أنواعها، وعلى رأسها المالية والقانونية.</span></span></p>
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: justify; text-justify: kashida; text-kashida: 0%; line-height: 115%;"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="line-height: 115%; font-family: "Sakkal Majalla";">ويقر الطرف الثاني: المذكور بياناته أعلاه والذي وقع أدناه:</span></strong><span lang="AR-SA" style="line-height: 115%; font-family: "Sakkal Majalla";"> أنه لا يطلب من الطرف الأول أي عهدة، أو التزام، أو مستحق مالي، أو قانوني على الإطلاق، وأنه تسلم كافة مستحقاته المالية من الطرف الأول، وأنه ليس لديه أي مستحقات متبقية من أي نوع.</span></span></p>
<p class="MsoNormal" dir="RTL" style="margin-bottom: 0in; text-align: justify; text-justify: kashida; text-kashida: 0%; line-height: 115%;">&nbsp;</p>
<h2 dir="RTL" style="margin: 0in 0in 6pt; text-align: center;"><strong><span lang="AR-SA" style="font-family: "Sakkal Majalla"; font-size: 10pt;">التوثيق</span></strong></h2>
<hr>
<div align="right">
<table class="MsoTableGrid" dir="rtl" style="border-collapse: collapse; border-style: none; border-color: initial; border-image: initial; width: 100%; border-spacing: 0px; margin-left: auto; margin-right: auto; height: 62.675px;" border="1" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 62.675px;">
<td style="width: 50%; border-style: none none none solid; border-image: initial; padding: 0in 5.4pt; border-color: initial initial initial windowtext;" valign="top" width="275">
<p class="MsoListParagraphCxSpFirst" dir="RTL" style="margin: 0in; text-align: center; line-height: 1.2;" align="center">&nbsp;</p>
<p class="MsoListParagraphCxSpFirst" dir="RTL" style="margin: 0in; text-align: center; line-height: 1.2;" align="center"><strong><span lang="AR-SA" style="font-size: 10pt; line-height: 115%; font-family: "Sakkal Majalla";">الطرف الأول</span></strong></p>
<p class="MsoListParagraphCxSpFirst" dir="RTL" style="margin: 0in; text-align: center; line-height: 1.2;" align="center">&nbsp;</p>
<p class="MsoListParagraphCxSpMiddle" dir="RTL" style="margin: 0in; mso-add-space: auto; text-align: center; line-height: 115%;" align="center"><strong><span dir="LTR" lang="EN-GB" style="font-size: 10pt; line-height: 115%; font-family: "Sakkal Majalla";">&nbsp;{{office_name}}</span></strong></p>
</td>
<td style="width: 50%; border-style: none; border-color: initial; border-image: initial; padding: 0in 5.4pt;" valign="top" width="275">
<p class="MsoListParagraphCxSpMiddle" dir="RTL" style="margin: 0in; line-height: 115%;" align="center">&nbsp;</p>
<p class="MsoListParagraphCxSpMiddle" dir="RTL" style="margin: 0in; line-height: 115%;" align="center"><strong><span lang="AR-SA" style="font-size: 10pt; line-height: 115%; font-family: "Sakkal Majalla";">الطرف الثاني</span></strong></p>
<p class="MsoListParagraphCxSpMiddle" dir="RTL" style="margin: 0in; line-height: 115%;" align="center">&nbsp;</p>
<p class="MsoListParagraphCxSpLast" dir="RTL" style="margin: 0in; mso-add-space: auto; text-align: center; line-height: 115%;" align="center"><span style="font-size: 10pt;"><strong><span lang="AR-SA" style="line-height: 115%; font-family: "Sakkal Majalla";">{{employee_name}}&nbsp;</span></strong></span></p>
</td>
</tr>
</tbody>
</table>
</div>
<p class="MsoNormal" dir="RTL" style="text-align: justify; line-height: 115%;">&nbsp;</p>',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);
    }
}
