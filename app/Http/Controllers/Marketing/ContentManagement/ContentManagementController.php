<?php

namespace App\Http\Controllers\Marketing\ContentManagement;

use App\Data\Marketing\ContentManagement\ContentManagementData;
use App\DataTables\Marketing\ContentManagement\ContentManagementDataTable;
use App\DataTables\Marketing\ContentManagement\SocialPublication\SocialPublicationDataTable;
use App\Enums\Marketing\ContentManagement\ContentStatus;
use App\Enums\Marketing\ContentManagement\PublicationStatus;
use App\Enums\Marketing\ContentManagement\MediaType;
use App\Enums\Marketing\ContentManagement\PublishType;
use App\Enums\Marketing\ContentManagement\RecurringType;
use App\Enums\Shared\WeekDay;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ContentManagement\ContentManagementRequest;
use App\Models\general_setting\Marketing\SettingsContentType;
use App\Models\general_setting\Marketing\SettingsPublishingPattern;
use App\Models\general_setting\Marketing\SettingsContentPurpose;
use App\Models\general_setting\SettingsSocial;
use App\Models\Marketing\ContentManagement\ContentManagement;
use App\Services\Marketing\ContentManagement\ContentManagementService;
use App\Services\Social\SocialConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentManagementController extends Controller
{
    public function __construct(
        private ContentManagementService $service,
        private SocialConnectionService $socialConnectionService
    ) {}

    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(ContentManagementDataTable $dataTable)
    {
        try {
            // Statistics
            $statusCounts = ContentManagement::query()
                ->select('publication_status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('publication_status')
                ->pluck('count', 'publication_status')
                ->toArray();

            $totalContent       = array_sum($statusCounts);
            $draftContent       = $statusCounts[PublicationStatus::Draft->value] ?? 0;
            $scheduledContent   = $statusCounts[PublicationStatus::Scheduled->value] ?? 0;
            $publishedContent   = $statusCounts[PublicationStatus::Published->value] ?? 0;

            // Filters
            $contentTypes       = SettingsContentType::select('id', 'name')->get();
            $publishingPatterns = SettingsPublishingPattern::select('id', 'name')->get();
            $contentStatus      = PublicationStatus::cases();

            return $dataTable->render('marketing.content_management.index', compact(
                'totalContent',
                'draftContent',
                'scheduledContent',
                'publishedContent',
                'contentTypes',
                'publishingPatterns',
                'contentStatus',

            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $contentTypes       = SettingsContentType::select('id', 'name')->get();
        $publishingPatterns = SettingsPublishingPattern::select('id', 'name')->get();
        $purposePieces      = SettingsContentPurpose::select('id', 'name')->get();
        $socials            = SettingsSocial::select('id', 'name')->get();
        $contentStatus      = PublicationStatus::cases();
        $mediaTypes         = MediaType::cases();
        $weekDays           = WeekDay::cases();
        $publishType        = PublishType::cases();
        $recurringType      = RecurringType::cases();

        return view('marketing.content_management.create', compact(
            'contentTypes',
            'publishingPatterns',
            'purposePieces',
            'socials',
            'contentStatus',
            'mediaTypes',
            'weekDays',
            'publishType',
            'recurringType'
        ));
    }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(ContentManagementRequest $request)
    {
        try {
            $dto = new ContentManagementData($request->validated());

            $this->service->createContent($dto, $request->file('media'));

            return redirect()->route('marketing.content-management.index')->with('success', 'تم إضافة المحتوى بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(ContentManagement $content_management, SocialPublicationDataTable $dataTable, Request $request)
    {
        $content_management->load([
            'contentType',
            'publishingPattern',
            'contentpurpose',
            'socials',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee',
        ]);

        $dataTable->content_management_id = $content_management->id;

        // -- Approval --//
        $approvalStages = $content_management->approvalRequest ? $content_management->approvalRequest->getApprovalStages()  : [];
        $isAutoApproved = empty($approvalStages) && ($content_management->status === ContentStatus::Approved);
        // -- Approval --//

        return $dataTable->render('marketing.content_management.show', compact(
            'content_management',
            'approvalStages',
            'isAutoApproved'
        ));
    }

    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(ContentManagement $content_management)
    {

        $contentTypes       = SettingsContentType::select('id', 'name')->get();
        $publishingPatterns = SettingsPublishingPattern::select('id', 'name')->get();
        $purposePieces      = SettingsContentPurpose::select('id', 'name')->get();
        $socials            = SettingsSocial::select('id', 'name')->get();
        $contentStatus      = PublicationStatus::cases();
        $mediaTypes         = MediaType::cases();
        $weekDays           = WeekDay::cases();
        $publishType        = PublishType::cases();
        $recurringType      = RecurringType::cases();


        return view('marketing.content_management.edit',  compact(
            'content_management',
            'contentTypes',
            'publishingPatterns',
            'purposePieces',
            'socials',
            'contentStatus',
            'mediaTypes',
            'weekDays',
            'publishType',
            'recurringType'
        ));
    }


    /*
    |============================================================================
    | Update
    |============================================================================
    */
    public function update(ContentManagementRequest $request, ContentManagement $content_management)
    {
        try {
            $dto = new ContentManagementData($request->validated());


            $this->service->updateContent($content_management->id, $dto, $request->file('media'));

            return redirect()->route('marketing.content-management.index')->with('success', 'تم تحديث المحتوى بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | Delete
    |============================================================================
    */
    public function destroy(int $id)
    {
        try {
            $this->service->deleteContent($id);

            return redirect()->route('marketing.content-management.index')->with('success', 'تم حذف المحتوى بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
