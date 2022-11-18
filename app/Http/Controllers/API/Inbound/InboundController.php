<?php

namespace App\Http\Controllers\API\Inbound;

use App\Http\Controllers\API\BaseAPIController;
use App\Http\Requests\API\Inbound\IndexInboundRequest;
use App\Http\Requests\API\Inbound\StoreInboundRequest;
use App\Http\Resources\API\Inbound\InboundResource;
use App\Repositories\API\InboundRepository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class InboundController extends BaseAPIController
{
    protected $inboundRepository;

    public function __construct(InboundRepository $inboundRepository)
    {
        $this->inboundRepository = $inboundRepository;
    }

    public function index(IndexInboundRequest $request)
    {
        $inbounds = $this->inboundRepository->getInbounds($request);

        return Response::json([
            'status' => true,
            'data' => InboundResource::collection($inbounds),
        ]);
    }

    public function store(StoreInboundRequest $request)
    {
        $inbound = $this->inboundRepository->create($request);

        return Response::json([
            'status' => true,
            'data' => new InboundResource($inbound),
        ]);
    }
}
