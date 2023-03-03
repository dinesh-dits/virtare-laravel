<?php

namespace App\Services\Api;

use App\Helper;
use App\Models\Staff\Staff;
use App\Models\Patient\PatientFamilyMember;
use App\Transformers\Staff\StaffTransformer;
use Exception;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Patient\PatientFamilyMemberTransformer;

class TeamService
{
    // Staff, Physician and Family Member Team List
    public function team($request, $patientId, $type, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();

            if (!$patientId) {
                if ($type == 'staff') {
                    $data = Staff::leftJoin('providerLocations', 'providerLocations.id', '=', 'staffs.providerLocationId')->with('type');
                    if ($provider) {
                        $data->where('staffs.providerId', $provider);
                    }
                    if ($providerLocation) {
                        $data->where(function ($query) use ($providerLocation) {
                            $query->where('staffs.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                        });
                    }
                    if (!$id) {
                        if ($request->all) {
                            $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) {
                                $query->where([['patientId', auth()->user()->patient->id], ['isCareTeam', 0]]);
                            })->orderBy('staffs.firstName', 'ASC')->get();
                            return fractal()->collection($data)->transformWith(new StaffTransformer(true))->toArray();
                        } else {
                            $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) {
                                $query->where([['patientId', auth()->user()->patient->id], ['isCareTeam', 0]]);
                            })->orderBy('staffs.firstName', 'ASC');
                            $data = $data->paginate(env('PER_PAGE', 20));
                            return fractal()->collection($data)->transformWith(new StaffTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                        }
                    } else {
                        $data = Staff::select('staffs.*')->where('udid', $id)->with('user')->first();
                        if (!empty($data)) {
                            return fractal()->item($data)->transformWith(new StaffTransformer(true))->toArray();
                        } else {
                            return response()->json(['message' => trans('messages.not_found')], 404);
                        }
                    }
                } elseif ($type == 'physician') {
                    $data = Staff::leftJoin('providerLocations', 'providerLocations.id', '=', 'staffs.providerLocationId')->with('type');
                    if ($provider) {
                        $data->where('staffs.providerId', $provider);
                    }
                    if ($providerLocation) {
                        $data->where(function ($query) use ($providerLocation) {
                            $query->where('staffs.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                        });
                    }
                    if (!$id) {
                        if ($request->all) {
                            $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) {
                                $query->where([['patientId', auth()->user()->patient->id], ['isCareTeam', 1]]);
                            })->orderBy('staffs.firstName', 'ASC')->get();
                            return fractal()->collection($data)->transformWith(new StaffTransformer(true))->toArray();
                        } else {
                            $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) {
                                $query->where([['patientId', auth()->user()->patient->id], ['isCareTeam', 1]]);
                            })->orderBy('staffs.firstName', 'ASC');
                            $data = $data->paginate(env('PER_PAGE', 20));
                            return fractal()->collection($data)->transformWith(new StaffTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                        }
                    } else {
                        $data = Staff::select('staffs.*')->where([['roleId', 3], ['udid', $id]])->with('user')->first();
                        if (!empty($data)) {
                            return fractal()->item($data)->transformWith(new StaffTransformer(true))->toArray();
                        } else {
                            return response()->json(['message' => trans('messages.not_found')], 404);
                        }
                    }
                } elseif ($type == 'familyMember') {
                    $data = PatientFamilyMember::leftJoin('providerLocations', 'providerLocations.id', '=', 'patientFamilyMembers.providerLocationId')->with('roles');
                    if ($provider) {
                        $data->where('patientFamilyMembers.providerId', $provider);
                    }
                    if ($providerLocation) {
                        $data->where(function ($query) use ($providerLocation) {
                            $query->where('patientFamilyMembers.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                        });
                    }
                    if (auth()->user()->roleId == 6) {
                        if (!$id) {
                            if ($request->all) {
                                $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->orderBy('firstName', 'ASC')->get();
                                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer(true))->toArray();
                            } else {
                                $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->orderBy('firstName', 'ASC')->paginate(env('PER_PAGE', 20));
                                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                            }
                        } else {
                            $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->where('udid', $id)->first();
                            if (!empty($data)) {
                                return fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer(true))->toArray();
                            } else {
                                return response()->json(['message' => trans('messages.not_found')], 404);
                            }
                        }
                    } elseif (auth()->user()->roleId == 4) {
                        $data = PatientFamilyMember::with('roles');
                        if ($provider) {
                            $data->where('providerId', $provider);
                        }
                        if ($providerLocation) {
                            $data->where(function ($query) use ($providerLocation) {
                                $query->where('patientFamilyMembers.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                            });
                        }
                        if (!$id) {
                            if ($request->all) {
                                $data = PatientFamilyMember::select('staffs.*')->with('roles')->where([['patientId', auth()->user()->patient->id]])->orderBy('firstName', 'ASC')->get();
                                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer(true))->toArray();
                            } else {
                                $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->where([['patientId', auth()->user()->patient->id]])->orderBy('firstName', 'ASC')->paginate(env('PER_PAGE', 20));
                                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                            }
                        } else {
                            $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->where([['patientId', auth()->user()->patient->id], ['udid', $id]])->first();
                            if (!empty($data)) {
                                return fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer(true))->toArray();
                            } else {
                                return response()->json(['message' => trans('messages.not_found')], 404);
                            }
                        }
                    }
                }
            } elseif ($patientId) {
                $patient = Helper::entity('patient', $patientId);
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    if ($type == 'staff') {
                        $data = Staff::leftJoin('providerLocations', 'providerLocations.id', '=', 'staffs.providerLocationId')->with('type');
                        if ($provider) {
                            $data->where('staffs.providerId', $provider);
                        }
                        if ($providerLocation) {
                            $data->where(function ($query) use ($providerLocation) {
                                $query->where('staffs.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                            });
                        }
                        if (!$id) {
                            if ($request->all) {
                                $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) use ($patient) {
                                    $query->where([['patientId', $patient], ['isCareTeam', 0]]);
                                })->orderBy('firstName', 'ASC')->orderBy('lastName', 'ASC')->get();
                                return fractal()->collection($data)->transformWith(new StaffTransformer(true))->toArray();
                            } else {
                                $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) use ($patient) {
                                    $query->where([['patientId', $patient], ['isCareTeam', 0]]);
                                })->orderBy('firstName', 'ASC')->orderBy('lastName', 'ASC');
                                $data = $data->paginate(env('PER_PAGE', 20));
                                return fractal()->collection($data)->transformWith(new StaffTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                            }
                        } else {
                            $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) use ($patient) {
                                $query->where([['patientId', $patient], ['isCareTeam', 0]]);
                            })->first();
                            if (!empty($data)) {
                                return fractal()->item($data)->transformWith(new StaffTransformer(true))->toArray();
                            } else {
                                return response()->json(['message' => trans('messages.not_found')], 404);
                            }
                        }
                    } elseif ($type == 'physician') {
                        $data = Staff::leftJoin('providerLocations', 'providerLocations.id', '=', 'staffs.providerLocationId')->with('type');
                        if ($provider) {
                            $data->where('staffs.providerId', $provider);
                        }
                        if ($providerLocation) {
                            $data->where(function ($query) use ($providerLocation) {
                                $query->where('staffs.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                            });
                        }
                        if (!$id) {
                            if ($request->all) {
                                $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) use ($patient) {
                                    $query->where([['patientId', $patient], ['isCareTeam', 1]]);
                                })->orderBy('staffs.firstName', 'ASC')->get();
                                return fractal()->collection($data)->transformWith(new StaffTransformer(true))->toArray();
                            } else {
                                $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) use ($patient) {
                                    $query->where([['patientId', $patient], ['isCareTeam', 1]]);
                                })->orderBy('staffs.firstName', 'ASC');
                                $data = $data->paginate(env('PER_PAGE', 20));
                                return fractal()->collection($data)->transformWith(new StaffTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                            }
                        } else {
                            $data = Staff::select('staffs.*')->whereHas('patientStaff', function ($query) use ($patient) {
                                $query->where([['patientId', $patient], ['isCareTeam', 1]]);
                            })->first();
                            if (!empty($data)) {
                                return fractal()->item($data)->transformWith(new StaffTransformer(true))->toArray();
                            } else {
                                return response()->json(['message' => trans('messages.not_found')], 404);
                            }
                        }
                    } elseif ($type == 'familyMember') {
                        $data = PatientFamilyMember::leftJoin('providerLocations', 'providerLocations.id', '=', 'patientFamilyMembers.providerLocationId')->with('roles');
                        if ($provider) {
                            $data->where('patientFamilyMembers.providerId', $provider);
                        }
                        if ($providerLocation) {
                            $data->where(function ($query) use ($providerLocation) {
                                $query->where('patientFamilyMembers.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                            });
                        }
                        if (!$id) {
                            if ($request->all) {
                                $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->where([['patientId', $patient]])->orderBy('firstName', 'ASC')->get();
                                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer(true))->toArray();
                            } else {
                                $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->where([['patientId', $patient]])->orderBy('firstName', 'ASC')->paginate(env('PER_PAGE', 20));
                                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                            }
                        } else {
                            $data = PatientFamilyMember::select('patientFamilyMembers.*')->with('roles')->where([['patientId', $patient], ['udid', $id]])->first();
                            if (!empty($data)) {
                                return fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer(true))->toArray();
                            } else {
                                return response()->json(['message' => trans('messages.not_found')], 404);
                            }
                        }
                    }
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
